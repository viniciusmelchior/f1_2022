<?php

namespace App\Http\Controllers\Site;

use App\Exports\CalendarioExport;
use App\Http\Controllers\Controller;
use App\Models\Site\CalendarioEtapa;
use App\Models\Site\CalendarioTemporada;
use App\Models\Site\Corrida;
use App\Services\CalendarioOficialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * MELHORIA 20261119: planejamento do calendário (formulário + grid em tempo real + anotações),
 * com a mesma lógica de ordem da planilha: ao mudar a posição de uma etapa, as demais se reorganizam
 * e a numeração é refeita 1, 2, 3... sem buracos nem repetições.
 */
class CalendarioController extends Controller
{
    private function temporadaDoUsuario($id): CalendarioTemporada
    {
        return CalendarioTemporada::where('user_id', Auth::user()->id)->findOrFail($id);
    }

    private function etapaDoUsuario($id): CalendarioEtapa
    {
        return CalendarioEtapa::whereHas('temporada', fn ($q) => $q->where('user_id', Auth::user()->id))->findOrFail($id);
    }

    private function etapasGrid(CalendarioTemporada $temporada): array
    {
        return $temporada->etapas()->get()->map->paraGrid()->values()->all();
    }

    /**
     * MELHORIA 20261119 (buracos): coloca $etapa no número $posicao exatamente como digitado (ex.: 11 com 7 etapas).
     * As outras NÃO são renumeradas; só se o número já estiver ocupado as etapas daquele ponto andam uma casa,
     * em cascata, até encontrar um número vago. Posições vagas ficam livres para etapas a definir.
     */
    private function reposicionar(CalendarioTemporada $temporada, CalendarioEtapa $etapa, int $posicao): void
    {
        $posicao = max(1, $posicao);
        $outras = $temporada->etapas()->where('id', '<>', $etapa->id)->where('ordem', '>=', $posicao)->get();

        $ocupado = $posicao;
        foreach ($outras as $outra) {           // em ordem crescente
            if ((int) $outra->ordem > $ocupado) {
                break;                          // achou um número vago: o resto não muda
            }
            $outra->ordem = $ocupado + 1;
            $outra->save();
            $ocupado = $outra->ordem;
        }

        if ((int) $etapa->ordem !== $posicao) {
            $etapa->ordem = $posicao;
            $etapa->save();
        }
    }

    /** Fecha os buracos: renumera 1, 2, 3... mantendo a ordem atual. */
    private function compactar(CalendarioTemporada $temporada): void
    {
        foreach ($temporada->etapas()->get()->values() as $i => $item) {
            if ((int) $item->ordem !== $i + 1) {
                $item->ordem = $i + 1;
                $item->save();
            }
        }
    }

    /**
     * MELHORIA 20261119: sugestões de autocompletar com o que já foi digitado em qualquer calendário do usuário
     * (não consulta as outras tabelas do sistema).
     */
    private function sugestoes(): array
    {
        $etapas = CalendarioEtapa::whereHas('temporada', fn ($q) => $q->where('user_id', Auth::user()->id))->get();
        $grupos = ['nomes' => [], 'sedes' => [], 'pistas' => [], 'continentes' => []];
        $add = function ($grupo, $valor) use (&$grupos) {
            $valor = trim((string) $valor);
            if ($valor !== '') {
                $grupos[$grupo][mb_strtolower($valor)] = $valor;
            }
        };
        foreach ($etapas as $e) {
            $add('nomes', $e->nome);
            $add('nomes', $e->sprint_nome);
            $add('sedes', $e->sede_exibicao);
            $add('sedes', $e->sprint_sede_exibicao);
            $add('pistas', $e->pista);
            $add('pistas', $e->sprint_pista);
            $add('continentes', $e->continente);
            $add('continentes', $e->sprint_continente);
        }
        foreach ($grupos as &$lista) {
            $lista = array_values($lista);
            natcasesort($lista);
            $lista = array_values($lista);
        }
        unset($lista);

        return $grupos;
    }

    public function index($id = null)
    {
        $temporadas = CalendarioTemporada::where('user_id', Auth::user()->id)->orderByDesc('id')->get(['id', 'nome']);
        $temporada = $id ? $this->temporadaDoUsuario($id) : ($temporadas->first() ? $this->temporadaDoUsuario($temporadas->first()->id) : null);
        $etapas = $temporada ? $this->etapasGrid($temporada) : [];
        $sugestoes = $this->sugestoes();

        return view('site.calendario.index', compact('temporadas', 'temporada', 'etapas', 'sugestoes'));
    }

    /** Nova temporada (vazia ou duplicada de outra). */
    public function storeTemporada(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:120',
            'duplicar_de' => 'nullable|integer',
            'copiar_anotacoes' => 'nullable',
        ]);

        $nova = DB::transaction(function () use ($dados, $request) {
            $nova = CalendarioTemporada::create(['user_id' => Auth::user()->id, 'nome' => trim($dados['nome'])]);

            if (!empty($dados['duplicar_de'])) {
                $origem = $this->temporadaDoUsuario($dados['duplicar_de']);
                if ($request->boolean('copiar_anotacoes')) {
                    $nova->anotacoes = $origem->anotacoes;
                    $nova->save();
                }
                foreach ($origem->etapas()->get() as $etapa) {
                    $copia = $etapa->replicate(['calendario_temporada_id']);
                    $copia->calendario_temporada_id = $nova->id;
                    $copia->save();
                }
            }

            return $nova;
        });

        return redirect()->route('calendario.index', [$nova->id])->with('success', 'Temporada criada.');
    }

    /** Renomear e/ou salvar anotações (JSON). */
    public function updateTemporada(Request $request, $id)
    {
        $temporada = $this->temporadaDoUsuario($id);
        $dados = $request->validate([
            'nome' => 'sometimes|required|string|max:120',
            'anotacoes' => 'sometimes|nullable|string',
        ]);
        $temporada->fill($dados)->save();

        return response()->json(['ok' => true, 'nome' => $temporada->nome, 'salvo_em' => now()->format('H:i:s')]);
    }

    public function destroyTemporada($id)
    {
        $this->temporadaDoUsuario($id)->delete();

        return redirect()->route('calendario.index')->with('success', 'Temporada excluída.');
    }

    /** Inclui ou altera uma etapa e devolve o calendário renumerado (JSON). */
    public function salvarEtapa(Request $request, $temporadaId)
    {
        $temporada = $this->temporadaDoUsuario($temporadaId);
        $regras = ['id' => 'nullable|integer', 'posicao' => 'required|integer|min:1', 'flg_sprint' => 'nullable|in:S,N',
            'duracao' => 'nullable|integer|min:1|max:600', 'sprint_duracao' => 'nullable|integer|min:1|max:600'];
        foreach (CalendarioEtapa::CAMPOS_TEXTO as $campo) {
            $regras[$campo] = str_contains($campo, 'observacoes') ? 'nullable|string|max:5000' : 'nullable|string|max:150';
        }
        // MELHORIA 20261119: só a ordem (posição) é obrigatória; os demais campos podem ficar vazios
        $dados = $request->validate($regras, [
            'posicao.required' => 'Informe a ordem (posição) da etapa.',
            'posicao.integer' => 'A ordem deve ser um número inteiro.',
            'posicao.min' => 'A ordem deve ser 1 ou maior.',
        ]);

        DB::transaction(function () use ($temporada, $dados) {
            $etapa = !empty($dados['id'])
                ? $temporada->etapas()->where('id', $dados['id'])->firstOrFail()
                : new CalendarioEtapa(['calendario_temporada_id' => $temporada->id]);

            foreach (CalendarioEtapa::CAMPOS_TEXTO as $campo) {
                $valor = isset($dados[$campo]) ? trim($dados[$campo]) : null;
                $etapa->{$campo} = $valor === '' ? null : $valor;
            }
            $etapa->flg_sprint = ($dados['flg_sprint'] ?? 'N') === 'S' ? 'S' : 'N';
            // MELHORIA 20261119: etapa sem sprint -> apaga os dados da sprint, se houver
            if ($etapa->flg_sprint === 'N') {
                foreach (CalendarioEtapa::CAMPOS_TEXTO as $campo) {
                    if (str_starts_with($campo, 'sprint_')) {
                        $etapa->{$campo} = null;
                    }
                }
                $dados['sprint_duracao'] = null;
            }
            $etapa->duracao = (int) ($dados['duracao'] ?? 0) ?: CalendarioEtapa::DURACAO_PADRAO;
            $etapa->sprint_duracao = (int) ($dados['sprint_duracao'] ?? 0) ?: CalendarioEtapa::SPRINT_DURACAO_PADRAO;

            // voltas vazias: calcula pelo tempo (corrida comum com +4s; sprint sem)
            if (blank($etapa->voltas)) {
                $v = CalendarioEtapa::calcularVoltas($etapa->tempo, $etapa->duracao, CalendarioEtapa::SEGUNDOS_EXTRA_CORRIDA);
                $etapa->voltas = $v === null ? null : (string) $v;
            }
            if ($etapa->flg_sprint === 'S' && blank($etapa->sprint_voltas)) {
                $v = CalendarioEtapa::calcularVoltas($etapa->sprint_tempo, $etapa->sprint_duracao);
                $etapa->sprint_voltas = $v === null ? null : (string) $v;
            }
            if (!$etapa->exists) {
                $etapa->ordem = 0;
            }
            $etapa->save();

            $this->reposicionar($temporada, $etapa, (int) $dados['posicao']);
        });

        return response()->json(['ok' => true, 'etapas' => $this->etapasGrid($temporada)]);
    }

    /**
     * MELHORIA 20261119: gera N etapas vazias (só com a ordem), numeradas a partir da maior ordem existente + 1.
     * Numa temporada vazia gera 1, 2, 3... N.
     */
    public function gerarEtapas(Request $request, $temporadaId)
    {
        $temporada = $this->temporadaDoUsuario($temporadaId);
        $dados = $request->validate(['quantidade' => 'required|integer|min:1|max:50'], [
            'quantidade.required' => 'Informe a quantidade de etapas.',
            'quantidade.max' => 'Gere no máximo 50 etapas por vez.',
            'quantidade.min' => 'A quantidade deve ser 1 ou maior.',
        ]);

        DB::transaction(function () use ($temporada, $dados) {
            $inicio = (int) $temporada->etapas()->max('ordem');
            for ($i = 1; $i <= (int) $dados['quantidade']; $i++) {
                CalendarioEtapa::create([
                    'calendario_temporada_id' => $temporada->id,
                    'ordem' => $inicio + $i,
                    'flg_sprint' => 'N',
                    'duracao' => CalendarioEtapa::DURACAO_PADRAO,
                    'sprint_duracao' => CalendarioEtapa::SPRINT_DURACAO_PADRAO,
                ]);
            }
        });

        return response()->json(['ok' => true, 'etapas' => $this->etapasGrid($temporada)]);
    }

    /** MELHORIA 20261119: botão "Fechar buracos" — renumera 1, 2, 3... (JSON). */
    public function renumerar($id)
    {
        $temporada = $this->temporadaDoUsuario($id);
        DB::transaction(fn () => $this->compactar($temporada));

        return response()->json(['ok' => true, 'etapas' => $this->etapasGrid($temporada)]);
    }

    /** "Mover para": muda o número da etapa (JSON). */
    public function moverEtapa(Request $request, $id)
    {
        $etapa = $this->etapaDoUsuario($id);
        $dados = $request->validate(['posicao' => 'required|integer|min:1']);
        DB::transaction(fn () => $this->reposicionar($etapa->temporada, $etapa, (int) $dados['posicao']));

        return response()->json(['ok' => true, 'etapas' => $this->etapasGrid($etapa->temporada)]);
    }

    public function destroyEtapa($id)
    {
        $etapa = $this->etapaDoUsuario($id);
        $temporada = $etapa->temporada;
        // MELHORIA 20261119: ao excluir, todas as etapas depois dela descem 1 número (os buracos que existirem
        // continuam no mesmo lugar relativo). Ex.: exclui a 1 -> a 2 vira 1, a 3 vira 2...
        DB::transaction(function () use ($etapa, $temporada) {
            $ordem = (int) $etapa->ordem;
            $etapa->delete();
            CalendarioEtapa::where('calendario_temporada_id', $temporada->id)->where('ordem', '>', $ordem)->decrement('ordem');
        });

        return response()->json(['ok' => true, 'etapas' => $this->etapasGrid($temporada)]);
    }

    /**
     * MELHORIA 20261126: dados do modal "cadastrar oficialmente" — procura no banco a temporada, o evento
     * e a pista da etapa (ou da sprint) do calendário (JSON).
     */
    public function dadosOficial(Request $request, $id, CalendarioOficialService $oficial)
    {
        $etapa = $this->etapaDoUsuario($id);
        $sprint = $request->query('parte') === 'sprint';
        abort_if($sprint && $etapa->flg_sprint !== 'S', 404);

        return response()->json($oficial->preparar($etapa, $sprint, Auth::user()->id));
    }

    /**
     * MELHORIA 20261126: lança a corrida na temporada oficial escolhida, com os mesmos campos de
     * CorridaController::store(). O calendário (rascunho) não é alterado.
     */
    public function cadastrarOficial(Request $request, $id, CalendarioOficialService $oficial)
    {
        $this->etapaDoUsuario($id);
        $usuario = Auth::user()->id;

        $dados = $request->validate([
            'temporada_id' => ['required', 'integer', Rule::exists('temporadas', 'id')->where('user_id', $usuario)],
            'evento_id' => ['required', 'integer', Rule::exists('eventos', 'id')->where('user_id', CalendarioOficialService::USUARIO_CATALOGO)],
            'pista_id' => ['required', 'integer', Rule::exists('pistas', 'id')->where('user_id', CalendarioOficialService::USUARIO_CATALOGO)],
            'ordem' => 'required|integer|min:1|max:999',
            'flg_sprint' => 'required|in:S,N',
            'confirmar' => 'nullable|boolean',
        ], [
            'temporada_id.required' => 'Selecione a temporada.',
            'evento_id.required' => 'Selecione o evento.',
            'pista_id.required' => 'Selecione a pista.',
            'ordem.required' => 'Informe a ordem da corrida.',
            'ordem.min' => 'A ordem deve ser 1 ou maior.',
        ]);

        if (!$request->boolean('confirmar')) {
            $repetidas = $oficial->repetidas($usuario, (int) $dados['temporada_id'], $dados['flg_sprint'], (int) $dados['ordem'], (int) $dados['evento_id']);
            if ($repetidas) {
                return response()->json(['ok' => false, 'repetidas' => $repetidas]);
            }
        }

        $corrida = new Corrida();
        $corrida->temporada_id = $dados['temporada_id'];
        $corrida->pista_id = $dados['pista_id'];
        $corrida->ordem = $dados['ordem'];
        $corrida->user_id = $usuario;
        $corrida->flg_sprint = $dados['flg_sprint'];
        $corrida->evento_id = $dados['evento_id'];
        $corrida->save();

        return response()->json([
            'ok' => true,
            'mensagem' => ($corrida->flg_sprint === 'S' ? 'Sprint' : 'Corrida') . ' "' . $corrida->evento->des_nome . '" (' . $corrida->pista->nome . ') lançada na '
                . $oficial->rotuloTemporada($corrida->temporada) . ', ordem ' . $corrida->ordem . '.',
            'url' => route('corridas.index', [$corrida->temporada_id]),
        ]);
    }

    /** MELHORIA 20261119: backup em Excel de todas as temporadas do calendário (uma aba por temporada). */
    public function exportar()
    {
        return Excel::download(new CalendarioExport(Auth::user()->id), 'calendario_backup_' . now()->format('Ymd_Hi') . '.xlsx');
    }
}
