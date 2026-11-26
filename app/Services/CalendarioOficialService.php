<?php

namespace App\Services;

use App\Models\Site\CalendarioEtapa;
use App\Models\Site\Corrida;
use App\Models\Site\Evento;
use App\Models\Site\Pista;
use App\Models\Site\Temporada;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * MELHORIA 20261126: leva uma etapa do calendário (rascunho, só texto) para o cadastro oficial.
 * Procura no banco a temporada, o evento e a pista pelo nome. Só considera "encontrado" quando o nome
 * confere (sem diferenciar maiúsculas, acentos e pontuação); não faz palpite por nome parecido.
 */
class CalendarioOficialService
{
    /** Eventos e pistas oficiais: o mesmo catálogo usado em CorridaController::adicionar(). */
    public const USUARIO_CATALOGO = 3;

    private const OK = 'ok';
    private const ATENCAO = 'atencao';
    private const NAO = 'nao';

    public function temporadas(int $usuarioId): Collection
    {
        return Temporada::with('ano')->where('user_id', $usuarioId)->orderByDesc('id')->get();
    }

    public function eventos(): Collection
    {
        return Evento::where('user_id', self::USUARIO_CATALOGO)->orderBy('des_nome')->get();
    }

    public function pistas(): Collection
    {
        return Pista::with('pais')->where('user_id', self::USUARIO_CATALOGO)->where('flg_ativo', 'S')->orderBy('nome')->get();
    }

    /** Dados do modal: listas dos selects + o que foi encontrado para cada um. */
    public function preparar(CalendarioEtapa $etapa, bool $sprint, int $usuarioId): array
    {
        $temporadas = $this->temporadas($usuarioId);
        $eventos = $this->eventos();
        $pistas = $this->pistas();

        $nome = trim((string) ($sprint ? $etapa->sprint_nome : $etapa->nome));
        $pista = trim((string) ($sprint ? $etapa->sprint_pista : $etapa->pista));
        $sede = $sprint ? $etapa->sprint_sede_exibicao : $etapa->sede_exibicao;

        return [
            'ordem' => (int) $etapa->ordem,
            'flg_sprint' => $sprint ? 'S' : 'N',
            'temporadas' => $temporadas->map(fn ($t) => [
                'id' => $t->id,
                'texto' => $this->rotuloTemporada($t),
                'finalizada' => $t->flg_finalizada === 'S',
            ])->values(),
            'eventos' => $eventos->map(fn ($e) => ['id' => $e->id, 'texto' => $e->des_nome])->values(),
            'pistas' => $pistas->map(fn ($p) => [
                'id' => $p->id,
                'texto' => $p->nome . ($p->pais ? ' · ' . $p->pais->des_nome : ''),
            ])->values(),
            'busca' => [
                'temporada' => $this->buscarTemporada($temporadas, trim((string) $etapa->temporada->nome)),
                'evento' => $this->buscarEvento($eventos, $nome),
                'pista' => $this->buscarPista($pistas, $pista, $sede),
            ],
        ];
    }

    /**
     * "68ª Temporada (2027)": o nome ("68ª Temporada") é comparado com des_temporada; o texto entre
     * parênteses é conferido com a referência ou o ano da temporada.
     */
    private function buscarTemporada(Collection $temporadas, string $nome): array
    {
        $referencia = '';
        $base = $nome;
        if (preg_match('/^(.*?)\s*\(([^()]*)\)\s*$/u', $nome, $m)) {
            [, $base, $referencia] = $m;
        }

        $base = $this->normalizarTemporada($base);
        $inteiro = $this->normalizarTemporada($nome);
        $achadas = $temporadas->filter(function ($t) use ($base, $inteiro) {
            $des = $this->normalizarTemporada($t->des_temporada);

            return $des !== '' && ($des === $base || $des === $inteiro);
        })->values();

        if ($achadas->isEmpty()) {
            return $this->resultado(self::NAO, null, "Temporada \"{$nome}\" não encontrada. Selecione outra.");
        }

        $ref = $this->normalizar($referencia);
        if ($ref !== '') {
            $comRef = $achadas->filter(fn ($t) => $this->normalizar($t->referencia) === $ref
                || $this->normalizar($t->ano->ano ?? '') === $ref)->values();
            if ($comRef->count() === 1) {
                return $this->resultado(self::OK, $comRef[0]->id, 'Encontrada pelo nome e pela referência do calendário.');
            }
            if ($comRef->count() > 1) {
                return $this->resultado(self::ATENCAO, $comRef[0]->id, "Há {$comRef->count()} temporadas com esse nome. Confira a selecionada.");
            }
        }

        if ($achadas->count() > 1) {
            return $this->resultado(self::ATENCAO, $achadas[0]->id, "Há {$achadas->count()} temporadas com esse nome. Confira a selecionada.");
        }

        if ($ref !== '') {
            $cadastrada = $achadas[0]->referencia ?: ($achadas[0]->ano->ano ?? '-');

            return $this->resultado(self::ATENCAO, $achadas[0]->id,
                "Nome encontrado, mas a referência não confere: calendário ({$referencia}), cadastro ({$cadastrada}). Confira.");
        }

        return $this->resultado(self::OK, $achadas[0]->id, 'Encontrada pelo nome.');
    }

    private function buscarEvento(Collection $eventos, string $nome): array
    {
        if ($nome === '') {
            return $this->resultado(self::NAO, null, 'O calendário não tem o nome desta etapa. Selecione o evento.');
        }

        $chave = $this->normalizarEvento($nome);
        $achados = $eventos->filter(fn ($e) => $this->normalizarEvento($e->des_nome) === $chave)->values();

        if ($achados->isEmpty()) {
            return $this->resultado(self::NAO, null, "Evento \"{$nome}\" não encontrado. Selecione outro.");
        }
        if ($achados->count() > 1) {
            return $this->resultado(self::ATENCAO, $achados[0]->id, "Há {$achados->count()} eventos com esse nome. Confira o selecionado.");
        }

        return $this->resultado(self::OK, $achados[0]->id, 'Evento encontrado.');
    }

    private function buscarPista(Collection $pistas, string $nome, ?string $sede): array
    {
        if ($nome === '') {
            return $this->resultado(self::NAO, null, 'O calendário não tem a pista desta etapa. Selecione a pista.');
        }

        $chave = $this->normalizar($nome);
        $achadas = $pistas->filter(fn ($p) => $this->normalizar($p->nome) === $chave)->values();

        if ($achadas->isEmpty()) {
            return $this->resultado(self::NAO, null, "Pista \"{$nome}\" não encontrada. Selecione outra.");
        }

        // pistas com o mesmo nome: o país da sede desempata
        if ($achadas->count() > 1 && filled($sede)) {
            $doPais = $achadas->filter(fn ($p) => $this->normalizar($p->pais->des_nome ?? '') === $this->normalizar($sede))->values();
            if ($doPais->isNotEmpty()) {
                $achadas = $doPais;
            }
        }
        if ($achadas->count() > 1) {
            return $this->resultado(self::ATENCAO, $achadas[0]->id, "Há {$achadas->count()} pistas com esse nome. Confira a selecionada.");
        }

        return $this->resultado(self::OK, $achadas[0]->id, 'Pista encontrada.');
    }

    /**
     * Corridas já lançadas na temporada que parecem ser esta (mesmo tipo e mesma ordem ou mesmo evento).
     * Serve só de aviso contra lançamento repetido.
     */
    public function repetidas(int $usuarioId, int $temporadaId, string $flgSprint, int $ordem, int $eventoId): array
    {
        return Corrida::with(['evento', 'pista'])
            ->where('user_id', $usuarioId)
            ->where('temporada_id', $temporadaId)
            ->where(fn ($q) => $flgSprint === 'S'
                ? $q->where('flg_sprint', 'S')
                : $q->whereNull('flg_sprint')->orWhere('flg_sprint', '<>', 'S'))
            ->where(fn ($q) => $q->where('ordem', $ordem)->orWhere('evento_id', $eventoId))
            ->orderBy('ordem')
            ->get()
            ->map(fn ($c) => 'Ordem ' . $c->ordem . ': ' . ($c->evento->des_nome ?? 'sem evento') . ' (' . ($c->pista->nome ?? 'sem pista') . ')')
            ->all();
    }

    public function rotuloTemporada(Temporada $t): string
    {
        $extra = $t->referencia ?: ($t->ano->ano ?? '');

        return $t->des_temporada . ($extra !== '' ? ' · ' . $extra : '') . ($t->flg_finalizada === 'S' ? ' (finalizada)' : '');
    }

    private function resultado(string $status, ?int $id, string $mensagem): array
    {
        return ['status' => $status, 'id' => $id, 'mensagem' => $mensagem];
    }

    /** minúsculas, sem acentos e pontuação, espaços únicos */
    private function normalizar($texto): string
    {
        $t = mb_strtolower(Str::ascii((string) $texto));

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $t));
    }

    /** "68ª Temporada" = "68a temporada" = "68 Temporada" */
    private function normalizarTemporada($texto): string
    {
        return preg_replace('/\b(\d+) ?[ao]\b/', '$1', $this->normalizar(str_replace(['ª', 'º', '°'], ' ', (string) $texto)));
    }

    /** "G.P. Brasil" = "Grande Prêmio do Brasil" = "GP do Brasil" (ignora da/do/de/das/dos) */
    private function normalizarEvento($texto): string
    {
        $t = $this->normalizar($texto);
        $t = preg_replace('/^(g p|grande premio)\b/', 'gp', $t);

        return trim(preg_replace('/\s+/', ' ', preg_replace('/\b(da|do|de|das|dos)\b/', ' ', $t)));
    }
}
