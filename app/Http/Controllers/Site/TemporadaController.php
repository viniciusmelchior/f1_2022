<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Ano;
use App\Models\Site\Corrida;
use App\Models\Site\Resultado;
use App\Models\Site\Temporada;
use App\Models\Site\Titulo;
use App\Support\SqlInput;
use App\Models\Site\TituloSprint;
use App\Models\Site\TituloPlayoff;
use App\Services\PlayoffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TemporadaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $temporadas = Temporada::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();

        $sprintsInfo = $this->resumoSprintsTemporadas(Auth::user()->id, $temporadas);
        $playoffInfo = $this->resumoPlayoffTemporadas(Auth::user()->id, $temporadas); // MELHORIA 20261110 (playoff)

        return view('site.temporadas.index', compact('temporadas', 'sprintsInfo', 'playoffInfo'));
    }

    /**
     * MELHORIA 20261110 (playoff): resumo do playoff por temporada para o modal da listagem.
     * Finalizadas com título leem titulos_playoff; as demais calculam o chaveamento (só temporadas com playoff).
     */
    private function resumoPlayoffTemporadas($usuario, $temporadas)
    {
        $comPlayoff = $temporadas->filter(fn ($t) => $t->temPlayoff());
        if ($comPlayoff->isEmpty()) {
            return [];
        }

        $titulos = TituloPlayoff::with(['pilotoEquipe.piloto', 'pilotoEquipe.equipe', 'vicePilotoEquipe.piloto', 'vicePilotoEquipe.equipe'])
            ->where('user_id', $usuario)
            ->whereIn('temporada_id', $comPlayoff->pluck('id'))
            ->get()
            ->keyBy('temporada_id');

        $icone = fn ($equipe) => $equipe ? [
            'img' => $equipe->imagem ? asset('images/' . $equipe->imagem) : null,
            'fundo' => \App\Models\Site\Equipe::corFundoIcone($equipe->des_cor, $equipe->imagem),
        ] : ['img' => null, 'fundo' => null];
        $doPilotoEquipe = fn ($pe) => $pe ? ['nome' => $pe->piloto->nomeCompleto(), 'equipe' => $pe->equipe->nome] + $icone($pe->equipe) : null;
        $doSeed = function ($s) use ($icone) {
            if (!$s) {
                return null;
            }
            $equipe = \App\Models\Site\Equipe::find(optional(\App\Models\Site\PilotoEquipe::find($s['pilotoEquipe_id']))->equipe_id);
            return ['nome' => trim($s['nome'] . ' ' . $s['sobrenome']), 'equipe' => $s['equipe']] + $icone($equipe);
        };

        $info = [];
        foreach ($comPlayoff as $temporada) {
            $finalizada = $temporada->flg_finalizada == 'S';
            $base = [
                'temporada' => $temporada->des_temporada . ($temporada->referencia ? ' · ' . $temporada->referencia : ''),
                'formato' => $temporada->playoffQtdClassificados() . ' classificados · ' . $temporada->playoffQtdClassificatorias() . ($temporada->playoffQtdClassificatorias() === 1 ? ' classificatória + ' : ' classificatórias + ') . $temporada->playoffCorridasMataMata() . ' de mata-mata',
                'finalizada' => $finalizada,
                'link' => route('temporadas.classificacao', [$temporada->id]) . '?aba=playoff',
                'campeao' => null,
                'vice' => null,
                'fase_atual' => null,
                'confrontos' => [],
            ];

            if ($finalizada && isset($titulos[$temporada->id])) {
                $t = $titulos[$temporada->id];
                $info[$temporada->id] = array_merge($base, [
                    'fase_atual' => 'Encerrado',
                    'campeao' => $doPilotoEquipe($t->pilotoEquipe),
                    'vice' => $doPilotoEquipe($t->vicePilotoEquipe),
                ]);
                continue;
            }

            $po = $this->montaPlayoff($usuario, $temporada, 1000);
            $confrontos = [];
            if ($po['iniciado'] && $po['rodada_atual']) {
                foreach ($po['rodadas'] as $r) {
                    if ($r['chave'] !== $po['rodada_atual']) {
                        continue;
                    }
                    foreach ($r['confrontos'] as $c) {
                        $confrontos[] = [
                            'a' => $c['a'] ? $c['a']['sobrenome'] : 'A definir',
                            'b' => $c['b'] ? $c['b']['sobrenome'] : 'A definir',
                            'pa' => $c['pontos_a'],
                            'pb' => $c['pontos_b'],
                            'vencedor' => $c['vencedor'] ? $c['vencedor']['sobrenome'] : null,
                        ];
                    }
                }
            }
            $info[$temporada->id] = array_merge($base, [
                'fase_atual' => $po['fase_atual'],
                'campeao' => $doSeed($po['campeao']),
                'vice' => $doSeed($po['vice']),
                'confrontos' => $confrontos,
            ]);
        }

        return $info;
    }

    /**
     * MELHORIA 20261110: resumo das sprints por temporada para o modal da listagem.
     * [temporada_id => qtd de sprints, finalizada, campeão (ou líder atual) piloto e construtor da classificação das sprints]
     * Finalizadas leem titulos_sprint; em andamento calculam a classificação das sprints (poucas temporadas).
     */
    private function resumoSprintsTemporadas($usuario, $temporadas)
    {
        $qtdSprints = Corrida::where('user_id', $usuario)
            ->where('flg_sprint', 'S')
            ->whereHas('resultado')
            ->groupBy('temporada_id')
            ->selectRaw('temporada_id, COUNT(*) as qtd')
            ->pluck('qtd', 'temporada_id');

        if ($qtdSprints->isEmpty()) {
            return [];
        }

        $titulos = TituloSprint::with(['pilotoEquipe.piloto', 'pilotoEquipe.equipe', 'equipe'])
            ->where('user_id', $usuario)
            ->get()
            ->keyBy('temporada_id');

        $icone = fn ($equipe) => $equipe ? [
            'img' => $equipe->imagem ? asset('images/' . $equipe->imagem) : null,
            'fundo' => \App\Models\Site\Equipe::corFundoIcone($equipe->des_cor, $equipe->imagem),
        ] : ['img' => null, 'fundo' => null];

        $info = [];
        foreach ($temporadas as $temporada) {
            if (!isset($qtdSprints[$temporada->id])) {
                continue;
            }

            $piloto = null;
            $equipe = null;
            $finalizada = $temporada->flg_finalizada == 'S';

            if ($finalizada && isset($titulos[$temporada->id])) {
                $t = $titulos[$temporada->id];
                $piloto = ['nome' => $t->pilotoEquipe->piloto->nomeCompleto(), 'equipe' => $t->pilotoEquipe->equipe->nome] + $icone($t->pilotoEquipe->equipe);
                $equipe = ['nome' => $t->equipe->nome] + $icone($t->equipe);
            } elseif (!$finalizada) {
                $classificacao = $this->montaClassificacao($usuario, $temporada, 1000, 'sprint');
                if (count($classificacao['resultadoPilotos']) > 0) {
                    $p = $classificacao['resultadoPilotos'][0];
                    $e = $classificacao['resultadoEquipes'][0];
                    $equipePiloto = \App\Models\Site\Equipe::find(optional(\App\Models\Site\PilotoEquipe::find($p->pilotoEquipe_ultima_id))->equipe_id);
                    $equipeLider = \App\Models\Site\Equipe::find($e->equipe_id);
                    $piloto = ['nome' => trim($p->nome . ' ' . $p->sobrenome), 'equipe' => $p->equipe_ultima, 'pontos' => (float) $p->total] + $icone($equipePiloto);
                    $equipe = ['nome' => $e->nome, 'pontos' => (float) $e->total] + $icone($equipeLider);
                }
            }

            $info[$temporada->id] = [
                'temporada' => $temporada->des_temporada . ($temporada->referencia ? ' · ' . $temporada->referencia : ''),
                'qtd' => (int) $qtdSprints[$temporada->id],
                'finalizada' => $finalizada,
                'piloto' => $piloto,
                'equipe' => $equipe,
                'link' => route('temporadas.classificacao', [$temporada->id]) . '?tipo=sprint',
            ];
        }

        return $info;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        return view('site.temporadas.form', compact('anos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $temporada = new Temporada();
        $temporada->des_temporada = $request->des_temporada;
        $temporada->user_id = Auth::user()->id;
        $temporada->ano_id = $request->ano_id;
        $temporada->observacoes = $request->observacoes;
        $temporada->referencia = $request->referencia;
        $temporada->flg_finalizada = 'N';
        $this->preenchePlayoff($temporada, $request); // MELHORIA 20261110 (playoff)

        $temporada->save();

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //aqui exibe as corridas
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $usuario = Auth::user()->id;
        $model = Temporada::where('id', $id)->first();
        $anos = Ano::where('user_id', Auth::user()->id)->get();
        //$retorno = $this->montaClassificacao($usuario, $model);

        return view('site.temporadas.form', compact('model', 'anos'));
    }

    public function classificacao_20260513($id)
    {

        $usuario = Auth::user()->id;
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first();

        $corridaAtual = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.temporada_id', $id)
            ->where('resultados.chegada', '<>', null)
            ->orderBy('corrida_id', 'desc')
            ->first();

        $totalCorridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->where('flg_sprint', 'N')->count();
        // $totalCorridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->count();

        if ($totalCorridas == 0 || $corridaAtual == null) {
            return redirect()->back()->with('error', 'Sem resultados registrados na temporada selecionada');
        }

        $corridaAtual = Corrida::find($corridaAtual->corrida_id);

        $retorno = $this->montaClassificacao($usuario, $temporada);
        $resultadosPilotos = $retorno['resultadoPilotos'];
        $resultadosEquipes = $retorno['resultadoEquipes'];

        $retornoClassico = $this->montaClassificacaoClassica($usuario, $temporada);
        $resultadosPilotosClassico = $retornoClassico['resultadoPilotosClassico'];
        $resultadosEquipesClassico = $retornoClassico['resultadoEquipesClassico'];

        $retornoInvertida = $this->montaClassificacaoInvertida($usuario, $temporada);
        $resultadosPilotosInvertida = $retornoInvertida['resultadoPilotosInvertida'];
        $resultadosEquipesInvertida = $retornoInvertida['resultadoEquipesInvertida'];

        $retornoAlternativa = $this->montaClassificacaoAlternativa($usuario, $temporada);
        $resultadosPilotosAlternativa = $retornoAlternativa['resultadoPilotosAlternativa'];
        $resultadosEquipesAlternativa = $retornoAlternativa['resultadoEquipesAlternativa'];

        return view('site.temporadas.classificacao', compact('corridaAtual', 'totalCorridas', 'temporada', 'resultadosPilotos', 'resultadosEquipes', 'resultadosPilotosClassico', 'resultadosEquipesClassico', 'resultadosPilotosInvertida', 'resultadosEquipesInvertida', 'resultadosPilotosAlternativa', 'resultadosEquipesAlternativa'));
    }

    /**
     * MELHORIA 20261110 (resumo): visão completa da temporada — números, estatísticas, curiosidades,
     * fatos que (não) aconteceram, cenários "e se" e manchetes. Cálculo em TemporadaResumoService.
     */
    public function resumo($id)
    {
        $usuario = Auth::user()->id;
        $temporada = Temporada::with('ano')->where('user_id', $usuario)->where('id', $id)->firstOrFail();
        $dados = (new \App\Services\TemporadaResumoService())->gerar($usuario, $temporada);

        // seletor de temporadas (mais recente primeiro) e navegação anterior/próxima
        $lista = Temporada::where('user_id', $usuario)->orderBy('id', 'DESC')->get(['id', 'des_temporada', 'referencia']);
        $posicao = $lista->search(fn ($t) => $t->id === $temporada->id);
        $navegacao = [
            'anterior' => $lista[$posicao + 1] ?? null, // mais antiga
            'proxima' => $posicao > 0 ? $lista[$posicao - 1] : null,
        ];

        return view('site.temporadas.resumo', compact('dados', 'temporada', 'lista', 'navegacao'));
    }

    public function classificacao($id)
    {
        // MELHORIA 20261110: botão "Sprints" só aparece se a temporada tiver sprints
        $temSprint = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $id)->where('flg_sprint', 'S')->exists();
        // MELHORIA 20261110 (playoff): aba "Playoffs" só nas temporadas com playoff habilitado
        $temPlayoff = (bool) optional(Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first())->temPlayoff();
        return view('site.temporadas.classificacao', compact('id', 'temSprint', 'temPlayoff'));
    }

    public function postClassificacao(Request $request)
    {
        $id = $request->id;

        $usuario = Auth::user()->id;
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first();
        $totalCorridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->where('flg_sprint', 'N')->count();
        // MELHORIA 20261105: a sprint tem a ordem da corrida principal do fim de semana; se a principal ainda
        // não foi cadastrada (ex.: 1N, 2S) a contagem acima fica 1 e o filtro "ordem <= 1" deixava a sprint de fora
        $totalCorridas = max($totalCorridas, (int) Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->max('ordem'));
        $corrida_ordem = $totalCorridas;

        $idCorrida = SqlInput::optionalId($request->idCorrida);
        if ($idCorrida !== null) {
            $corrida_ordem = $idCorrida;
        }

        // MELHORIA 20261110: classificação geral sem sprints; tipo=sprint mostra só as sprints
        $tipo = $request->tipo === 'sprint' ? 'sprint' : 'principal';

        // MELHORIA 20261110 (nav sprint): no modo sprint a navegação percorre só as etapas que têm sprint
        $navegacao = null;
        if ($tipo === 'sprint') {
            $ordensSprint = Corrida::where('user_id', $usuario)
                ->where('temporada_id', $temporada->id)
                ->where('flg_sprint', 'S')
                ->orderBy('ordem')
                ->pluck('ordem')
                ->map(fn ($o) => (int) $o)
                ->unique()
                ->values();

            if ($ordensSprint->isEmpty()) {
                $tipo = 'principal'; // temporada sem sprints: mantém o comportamento normal
            } else {
                if ($request->idCorrida == null) {
                    // abertura direto nas sprints: última sprint com resultado (ou a última cadastrada)
                    $alvo = (int) Corrida::where('user_id', $usuario)->where('temporada_id', $temporada->id)
                        ->where('flg_sprint', 'S')->whereHas('resultado')->max('ordem') ?: $ordensSprint->last();
                } else {
                    // etapa pedida sem sprint (ex.: troca Geral -> Sprints): última sprint até essa etapa
                    $pedido = (int) $request->idCorrida;
                    $alvo = $ordensSprint->filter(fn ($o) => $o <= $pedido)->last() ?? $ordensSprint->first();
                }
                $posicao = $ordensSprint->search($alvo);
                $corrida_ordem = $alvo;
                $navegacao = [
                    'anterior' => $ordensSprint[$posicao - 1] ?? null,
                    'proximo' => $ordensSprint[$posicao + 1] ?? null,
                    'posicao' => $posicao + 1,
                    'total' => $ordensSprint->count(),
                ];
            }
        }

        // $corridaAtual = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
        //     ->join('eventos', 'corridas.evento_id', '=', 'eventos.id')
        //     ->where('resultados.user_id', Auth::user()->id)
        //     ->where('corridas.temporada_id', $id)
        //     ->where('resultados.chegada', '<>', null)
        //     ->where('corridas.ordem','=', $corrida_ordem)
        //     ->orderBy('corrida_id', 'desc')
        //     ->first();

        //ajustado para evitar bug da corrida atual
        $corridaAtual = Corrida::where('user_id', Auth::user()->id)
            ->where('corridas.ordem', '=', $corrida_ordem)
            ->where('temporada_id', $id)
            ->when($navegacao, fn ($q) => $q->where('flg_sprint', 'S')) // MELHORIA 20261110: cabeçalho mostra a própria sprint
            ->orderBy('id', 'desc')
            ->first();

            // dd($corridaAtual);

        $dados = [
            "ordem" => $corridaAtual->ordem,
            "total_corridas" => $totalCorridas,
            "des_temporada" => $temporada->des_temporada,
            "ano" => $temporada->referencia,
            // "circuito" => $corridaAtual->corrida->pista->nome,
            "circuito" => $corridaAtual->pista?->nome ?? '-',
            "evento" => $corridaAtual->evento?->des_nome ?? '-',
            "contagem_corrida" => $corridaAtual->ordem . " de " . $totalCorridas,
            "url_bandeira" => $corridaAtual->pista?->pais?->imagem,
            "pilotos" => [
                // ["name" => "Max Verstappen", "team" => "Red Bull", "short" => "rbr", "color" => "#3671C6", "points" => 393],
                // ["name" => "Lando Norris", "team" => "McLaren", "short" => "mcl", "color" => "#FF8000", "points" => 374],
                // ["name" => "Charles Leclerc", "team" => "Ferrari", "short" => "fer", "color" => "#E80020", "points" => 356]
            ],
            "equipes" => [
                // ["name" => "McLaren", "short" => "mcl", "color" => "#FF8000", "points" => 666],
                // ["name" => "Ferrari", "short" => "fer", "color" => "#E80020", "points" => 652],
                // ["name" => "Red Bull", "short" => "rbr", "color" => "#3671C6", "points" => 589]
            ]
        ];

        $dados['tipo'] = $tipo;

        // MELHORIA 20261110 (nav sprint): contagem e setas pelas sprints ("2 de 6")
        if ($navegacao) {
            $dados['total_corridas'] = $navegacao['total'];
            $dados['contagem_corrida'] = $navegacao['posicao'] . ' de ' . $navegacao['total'];
            $dados['navegacao'] = $navegacao;
        }

        $retorno = $this->montaClassificacao($usuario, $temporada, $corrida_ordem, $tipo);

        //Monta os dados dos pilotos
        foreach ($retorno['resultadoPilotos'] as $resultadoPiloto) {
            $dados['pilotos'][] = [
                'nome' => $resultadoPiloto->nome,
                'sobrenome' => $resultadoPiloto->sobrenome,
                'logoEquipe' => $resultadoPiloto->equipe_ultima_imagem, // MELHORIA 20261105: equipe da última corrida
                'foto' => $resultadoPiloto->piloto_imagem,
                'color' => $resultadoPiloto->equipe_ultima_cor,
                'pontos' => $resultadoPiloto->total,
                'corFundo' => "#ff8700",
                'corTexto' => "#000000"
            ];
        }

        //Monta os dados das equipes
        foreach ($retorno['resultadoEquipes'] as $equipe) {
            $dados['equipes'][] = [
                'nome' => $equipe->nome,
                'logoEquipe' => $equipe->imagem,
                'color' => $equipe->cor,
                'pontos' => $equipe->total,
                'corFundo' => "#ff8700",
                'corTexto' => "#000000"
            ];
        }

        // MELHORIA 20261110 (playoff): chaveamento até a etapa exibida (sempre pelas corridas principais)
        if ($temporada->temPlayoff()) {
            $dados['playoff'] = $this->montaPlayoff($usuario, $temporada, $corrida_ordem, $tipo === 'principal' ? $retorno['resultadoPilotos'] : null);
            foreach ($retorno['resultadoPilotos'] as $i => $resultadoPiloto) {
                $dados['pilotos'][$i]['piloto_id'] = (int) $resultadoPiloto->piloto_id;
            }
        }

        return response()->json([
            'message' => 'OK',
            'dados' => $dados
        ]);
    }

    /**
     * MELHORIA 20261110: $tipo define quais corridas entram na soma de pontos:
     *  - 'todas'     => comportamento original (corridas + sprints). Usado no cálculo dos títulos (update).
     *  - 'principal' => só corridas principais (sprints não somam pontos).
     *  - 'sprint'    => só sprints (classificação exclusiva das sprints; desempate por posições nas sprints).
     */
    public function montaClassificacao($usuario, $temporada, $corrida_ordem = 1000, $tipo = 'todas')
    {
        // Values are concatenated into the SQL below: only integers get through.
        $temporadaId = (int) $temporada->id;
        $usuarioId = (int) $usuario;
        $ordemLimite = (int) $corrida_ordem;

        $cont = 1;
        $queryCountChegadaPilotos = '';
        $queryCountOrderByPilotos = '';
        $queryCountChegadaEquipes = '';
        $queryCountOrderByEquipes = '';

        // MELHORIA 20261110: filtro de tipo de corrida (flg_sprint pode ser NULL em corridas antigas = principal)
        $flgPosicoes = $tipo === 'sprint' ? 'S' : 'N';
        $filtroTipo = '';
        if ($tipo === 'principal') {
            $filtroTipo = ' AND (corridas.flg_sprint IS NULL OR corridas.flg_sprint <> "S")';
        } elseif ($tipo === 'sprint') {
            $filtroTipo = ' AND corridas.flg_sprint = "S"';
        }

        while ($cont <= 43) {
            if ($cont == 43) {
                $queryCountChegadaPilotos .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "' . $flgPosicoes . '") AS posicao_' . $cont . '';
                $queryCountChegadaEquipes .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "' . $flgPosicoes . '") AS posicao_' . $cont . '';
            } else {
                $queryCountChegadaPilotos .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "' . $flgPosicoes . '") AS posicao_' . $cont . ',';
                $queryCountChegadaEquipes .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "' . $flgPosicoes . '") AS posicao_' . $cont . ',';
            }
            $queryCountOrderByPilotos .= ', posicao_' . $cont . ' desc';
            $queryCountOrderByEquipes .= ', posicao_' . $cont . ' desc';
            $cont++;
        }

        // MELHORIA 20261105: desempate pelas SPRINTS, aplicado DEPOIS de todos os critérios das corridas comuns
        // (pontos -> 1ºs, 2ºs ... 43ºs em corridas comuns -> 1ºs, 2ºs ... 43ºs em sprints).
        // Só decide quem continua empatado: nenhuma ordem que já era definida muda.
        // Conta as sprints da temporada até a corrida consultada, somando todos os vínculos do piloto (troca de equipe).
        $colunasSprint = [];
        $selectSprint = [];
        $orderBySprint = '';
        for ($pos = 1; $pos <= 43; $pos++) {
            $colunasSprint[] = 'SUM(r_sp.chegada = ' . $pos . ') AS sprint_posicao_' . $pos;
            $selectSprint[] = 'COALESCE(MAX(sp.sprint_posicao_' . $pos . '), 0) AS sprint_posicao_' . $pos;
            $orderBySprint .= ', sprint_posicao_' . $pos . ' desc';
        }
        $filtroSprint = 'FROM resultados r_sp
                            JOIN corridas c_sp ON c_sp.id = r_sp.corrida_id
                            JOIN piloto_equipes pe_sp ON pe_sp.id = r_sp.pilotoEquipe_id
                            WHERE c_sp.temporada_id = ' . $temporadaId . '
                            AND r_sp.user_id = ' . $usuarioId . '
                            AND c_sp.ordem <= ' . $ordemLimite . '
                            AND c_sp.flg_sprint = "S"';
        $joinSprintPilotos = 'LEFT JOIN (SELECT pe_sp.piloto_id AS sp_piloto_id, ' . implode(', ', $colunasSprint) . ' ' . $filtroSprint . ' GROUP BY pe_sp.piloto_id) sp ON sp.sp_piloto_id = pilotos.id';
        $joinSprintEquipes = 'LEFT JOIN (SELECT pe_sp.equipe_id AS sp_equipe_id, ' . implode(', ', $colunasSprint) . ' ' . $filtroSprint . ' GROUP BY pe_sp.equipe_id) sp ON sp.sp_equipe_id = equipes.id';
        // MELHORIA 20261110: na classificação só de sprints as colunas posicao_X já contam as sprints
        if ($tipo === 'sprint') {
            $orderBySprint = '';
        }

        $resultadosPilotos = DB::select('
                            SELECT
                            piloto_id,
                            piloto_equipes.id AS pilotoEquipe_id,
                            pilotos.nome,
                            pilotos.sobrenome,
                            equipes.nome AS equipe,
                            equipes.imagem as equipe_imagem,
                            pilotos.imagem as piloto_imagem,
                            equipes.des_cor as cor,
                            SUM(pontuacao) AS total,
                            ' . $queryCountChegadaPilotos . ',
                            ' . implode(', ', $selectSprint) . '
                            FROM resultados
                            JOIN piloto_equipes ON piloto_equipes.id = resultados.pilotoEquipe_id
                            JOIN pilotos ON pilotos.id = piloto_equipes.piloto_id
                            JOIN equipes ON equipes.id = piloto_equipes.equipe_id
                            JOIN corridas ON corridas.id = resultados.corrida_id
                            JOIN temporadas ON temporadas.id = corridas.temporada_id
                            ' . $joinSprintPilotos . '
                            WHERE temporadas.id =  ' . $temporadaId . '
                            AND resultados.user_id =  ' . $usuarioId . '
                            and corridas.ordem <= ' . $ordemLimite . $filtroTipo . '
                            GROUP BY pilotos.id
                            ORDER BY total DESC ' . $queryCountOrderByPilotos . $orderBySprint . ';    
        ');

        //backup da ordenação 
        //GROUP BY piloto_equipes.piloto_id, piloto_equipes.id, pilotos.nome, pilotos.sobrenome, equipes.nome, equipes.imagem

        // dd($resultadosPilotos);

        // dd($resultadosPilotos, $queryCountOrderByPilotos);
        // dd($queryCountChegadaPilotos, $queryCountOrderByPilotos);

        // dd($queryCountChegadaEquipes);

        $resultadosEquipes = DB::select('select equipe_id, equipes.des_cor as cor, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total,' . $queryCountChegadaEquipes . ', ' . implode(', ', $selectSprint) . ' from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        ' . $joinSprintEquipes . '
                                        where temporadas.id = ' . $temporadaId . '
                                        and resultados.user_id = ' . $usuarioId . '
                                        and corridas.ordem <= ' . $ordemLimite . $filtroTipo . '
                                        group by piloto_equipes.equipe_id
                                        order by total desc ' . $queryCountOrderByEquipes . $orderBySprint);

        // MELHORIA 20261105: equipe da última corrida disputada (até a ordem consultada) de cada piloto.
        // Piloto que trocou de equipe no ano tem dois vínculos e o GROUP BY acima devolve qualquer um deles.
        $ultimasEquipes = $this->equipesDaUltimaCorrida($usuario, $temporada->id, $corrida_ordem);
        foreach ($resultadosPilotos as $resultadoPiloto) {
            $ultima = $ultimasEquipes[$resultadoPiloto->piloto_id] ?? null;
            $resultadoPiloto->pilotoEquipe_ultima_id = $ultima->pilotoEquipe_id ?? $resultadoPiloto->pilotoEquipe_id;
            $resultadoPiloto->equipe_ultima = $ultima->equipe ?? $resultadoPiloto->equipe;
            $resultadoPiloto->equipe_ultima_imagem = $ultima->imagem ?? $resultadoPiloto->equipe_imagem;
            $resultadoPiloto->equipe_ultima_cor = $ultima->cor ?? $resultadoPiloto->cor;
        }

        return [
            'resultadoPilotos' => $resultadosPilotos,
            'resultadoEquipes' => $resultadosEquipes,
            'piloto_campeao' => $resultadosPilotos,
            'equipe_campea' => $resultadosEquipes
        ];
    }
    /**
     * MELHORIA 20261105: [piloto_id => equipe do último resultado com chegada na temporada]
     * Ordem: maior "ordem"; no mesmo fim de semana a principal (N) vem depois da sprint (S).
     */
    private function equipesDaUltimaCorrida($usuario, $temporadaId, $corrida_ordem = 1000)
    {
        $linhas = DB::table('resultados')
            ->join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
            ->join('equipes', 'equipes.id', '=', 'piloto_equipes.equipe_id')
            ->where('corridas.temporada_id', $temporadaId)
            ->where('resultados.user_id', $usuario)
            ->where('corridas.ordem', '<=', $corrida_ordem)
            ->whereNotNull('resultados.chegada')
            ->orderBy('corridas.ordem', 'desc')
            ->orderBy('corridas.flg_sprint', 'asc')
            ->orderBy('corridas.id', 'desc')
            ->select('piloto_equipes.piloto_id', 'piloto_equipes.id as pilotoEquipe_id', 'equipes.nome as equipe', 'equipes.imagem', 'equipes.des_cor as cor')
            ->get();

        $porPiloto = [];
        foreach ($linhas as $linha) {
            if (!isset($porPiloto[$linha->piloto_id])) {
                $porPiloto[$linha->piloto_id] = $linha;
            }
        }
        return $porPiloto;
    }

    public function montaClassificacaoClassica($usuario, $temporada)
    {
        // Values are concatenated into the SQL below: only integers get through.
        $temporadaId = (int) $temporada->id;
        $usuarioId = (int) $usuario;
        $resultadosPilotosClassico = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id,pilotos.nome,pilotos.sobrenome,equipes.imagem, equipes.nome as equipe, sum(pontuacao_classica) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join pilotos on pilotos.id = piloto_equipes.piloto_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = ' . $temporadaId . '
                and resultados.user_id = ' . $usuarioId . '
                group by piloto_equipes.piloto_id
                order by total desc');

        $resultadosEquipesClassico = DB::select('select equipe_id, equipes.imagem, equipes.nome as nome, sum(pontuacao_classica) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = ' . $temporadaId . '
                and resultados.user_id = ' . $usuarioId . '
                group by piloto_equipes.equipe_id
                order by total desc');

        return [
            'resultadoPilotosClassico' => $resultadosPilotosClassico,
            'resultadoEquipesClassico' => $resultadosEquipesClassico,
        ];
    }

    public function montaClassificacaoInvertida($usuario, $temporada)
    {
        // Values are concatenated into the SQL below: only integers get through.
        $temporadaId = (int) $temporada->id;
        $usuarioId = (int) $usuario;
        $resultadosPilotosInvertida = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id, pilotos.nome, pilotos.sobrenome,equipes.imagem, equipes.nome as equipe, sum(pontuacao_invertida) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join pilotos on pilotos.id = piloto_equipes.piloto_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = ' . $temporadaId . '
                and resultados.user_id = ' . $usuarioId . '
                group by piloto_equipes.piloto_id
                order by total desc');

        $resultadosEquipesInvertida = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao_invertida) as total from resultados
                join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                join equipes on equipes.id = piloto_equipes.equipe_id
                join corridas on corridas.id = resultados.corrida_id
                join temporadas on temporadas.id = corridas.temporada_id
                where temporadas.id = ' . $temporadaId . '
                and resultados.user_id = ' . $usuarioId . '
                group by piloto_equipes.equipe_id
                order by total desc');

        return [
            'resultadoPilotosInvertida' => $resultadosPilotosInvertida,
            'resultadoEquipesInvertida' => $resultadosEquipesInvertida,
        ];
    }

    /**Utilizada para o calculo de pontuação das corridas sprint */
    public function montaClassificacaoAlternativa($usuario, $temporada)
    {
        // Values are concatenated into the SQL below: only integers get through.
        $temporadaId = (int) $temporada->id;
        $usuarioId = (int) $usuario;
        // $resultadosPilotosAlternativa = DB::select('select piloto_id,piloto_equipes.id as pilotoEquipe_id, pilotos.nome, pilotos.sobrenome, equipes.imagem, equipes.nome as equipe, sum(pontuacao) as total from resultados
        // join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
        // join pilotos on pilotos.id = piloto_equipes.piloto_id
        // join equipes on equipes.id = piloto_equipes.equipe_id
        // join corridas on corridas.id = resultados.corrida_id
        // join temporadas on temporadas.id = corridas.temporada_id
        // where temporadas.id = '.$temporada->id.'
        // and resultados.user_id = '.$usuario.'
        // and corridas.flg_sprint = "S"
        // group by piloto_equipes.piloto_id
        // order by total desc');

        $cont = 1;
        $queryCountChegadaPilotos = '';
        $queryCountOrderByPilotos = '';
        $queryCountChegadaEquipes = '';
        $queryCountOrderByEquipes = '';

        while ($cont <= 43) {
            if ($cont == 43) {
                $queryCountChegadaPilotos .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "S") AS posicao_' . $cont . '';
                $queryCountChegadaEquipes .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "S") AS posicao_' . $cont . '';
            } else {
                $queryCountChegadaPilotos .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "S") AS posicao_' . $cont . ',';
                $queryCountChegadaEquipes .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "S") AS posicao_' . $cont . ',';
            }
            $queryCountOrderByPilotos .= ', posicao_' . $cont . ' desc';
            $queryCountOrderByEquipes .= ', posicao_' . $cont . ' desc';
            $cont++;
        }

        $resultadosPilotosAlternativa = DB::select('
                                    SELECT
                                    piloto_id,
                                    piloto_equipes.id AS pilotoEquipe_id,
                                    pilotos.nome,
                                    pilotos.sobrenome,
                                    equipes.nome AS equipe,
                                    equipes.imagem,
                                    SUM(pontuacao) AS total,
                                    ' . $queryCountChegadaPilotos . '
                                    FROM resultados
                                    JOIN piloto_equipes ON piloto_equipes.id = resultados.pilotoEquipe_id
                                    JOIN pilotos ON pilotos.id = piloto_equipes.piloto_id
                                    JOIN equipes ON equipes.id = piloto_equipes.equipe_id
                                    JOIN corridas ON corridas.id = resultados.corrida_id
                                    JOIN temporadas ON temporadas.id = corridas.temporada_id
                                    WHERE temporadas.id =  ' . $temporadaId . '
                                    AND resultados.user_id =  ' . $usuarioId . '
                                    AND corridas.flg_sprint = "S"
                                    GROUP BY pilotos.id
                                    ORDER BY total DESC ' . $queryCountOrderByPilotos . ';    
                ');

        // $resultadosEquipesAlternativa = DB::select('select equipe_id,equipes.imagem, equipes.nome as nome, sum(pontuacao) as total from resultados
        // join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
        // join equipes on equipes.id = piloto_equipes.equipe_id
        // join corridas on corridas.id = resultados.corrida_id
        // join temporadas on temporadas.id = corridas.temporada_id
        // where temporadas.id = '.$temporada->id.'
        // and resultados.user_id = '.$usuario.'
        // and corridas.flg_sprint = "S"
        // group by piloto_equipes.equipe_id
        // order by total desc');

        $resultadosEquipesAlternativa = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total,' . $queryCountChegadaEquipes . ' from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = ' . $temporadaId . '
                                        and resultados.user_id = ' . $usuarioId . '
                                        AND corridas.flg_sprint = "S"
                                        group by piloto_equipes.equipe_id
                                        order by total desc ' . $queryCountOrderByEquipes);



        return [
            'resultadoPilotosAlternativa' => $resultadosPilotosAlternativa,
            'resultadoEquipesAlternativa' => $resultadosEquipesAlternativa,
        ];
    }

    /**
     * MELHORIA 20261110 (playoff): checkbox "Habilitar playoff" + total de corridas principais.
     * Precisa de pelo menos 1 classificatória (qtd > corridas de mata-mata); sem isso o playoff fica desabilitado.
     * playoff_classificados: 8 (quartas, semi, final = 9 corridas) ou 16 (+ oitavas = 12 corridas).
     */
    private function preenchePlayoff(Temporada $temporada, Request $request)
    {
        $qtd = (int) $request->qtd_corridas;
        $classificados = (int) $request->playoff_classificados === 16 ? 16 : 8;
        $habilitado = $request->flg_playoff === 'S' && $qtd > Temporada::playoffCorridasMataMataPara($classificados);
        $temporada->flg_playoff = $habilitado ? 'S' : 'N';
        $temporada->qtd_corridas = $qtd > 0 ? $qtd : null;
        $temporada->playoff_classificados = $classificados;
    }

    /**
     * MELHORIA 20261110 (playoff): chaveamento até a etapa $corrida_ordem (só corridas principais).
     * $classificacaoAtual = resultadoPilotos principal até essa etapa, quando já calculado.
     */
    public function montaPlayoff($usuario, Temporada $temporada, $corrida_ordem = 1000, $classificacaoAtual = null)
    {
        if (!$temporada->temPlayoff()) {
            return null;
        }
        if ($classificacaoAtual === null) {
            $classificacaoAtual = $this->montaClassificacao($usuario, $temporada, $corrida_ordem, 'principal')['resultadoPilotos'];
        }

        return (new PlayoffService())->montar(
            $usuario,
            $temporada,
            $corrida_ordem,
            $classificacaoAtual,
            fn ($ordem) => $this->montaClassificacao($usuario, $temporada, $ordem, 'principal')['resultadoPilotos']
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $temporada = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $temporada->des_temporada = $request->des_temporada;
        $temporada->user_id = Auth::user()->id;
        $temporada->ano_id = $request->ano_id;
        $usuario = Auth::user()->id;
        $temporada->observacoes = $request->observacoes;
        $temporada->referencia = $request->referencia;
        $this->preenchePlayoff($temporada, $request); // MELHORIA 20261110 (playoff)
        //pesquisa pela classificação atualizada
        $retorno = $this->montaClassificacao($usuario, $temporada);

        if ($request->has('flg_finalizada')) {
            $temporada->flg_finalizada = $request->flg_finalizada;
        } else {
            $temporada->flg_finalizada = 'N';
        }

        $temporada->update();

        $this->recalcularTitulos($usuario);

        return redirect()->route('temporadas.index');
    }

    /**
     * MELHORIA 20261110: recalcula os títulos de todas as temporadas finalizadas do usuário.
     *  - titulos        => classificação geral SEM os pontos das sprints
     *  - titulos_sprint => classificação exclusiva das sprints (só temporadas que tiveram sprint)
     * (antes ficava inline no update() e somava os pontos das sprints)
     */
    public function recalcularTitulos($usuario)
    {
        //apaga todos os titulos
        Titulo::where('user_id', $usuario)->delete();
        TituloSprint::where('user_id', $usuario)->delete();
        TituloPlayoff::where('user_id', $usuario)->delete(); // MELHORIA 20261110 (playoff)
        $temporadas = Temporada::where('user_id', $usuario)->where('flg_finalizada', 'S')->get();

        foreach ($temporadas as $temporada) {
            $retorno = $this->montaClassificacao($usuario, $temporada, 1000, 'principal');

            if (count($retorno['piloto_campeao']) > 0) {
                $titulo = new Titulo();
                $titulo->temporada_id = $temporada->id;
                $titulo->pilotoEquipe_id = $retorno['piloto_campeao'][0]->pilotoEquipe_id;
                $titulo->equipe_id = $retorno['equipe_campea'][0]->equipe_id;
                $titulo->user_id = $usuario;
                $titulo->save();
            }

            $retornoSprint = $this->montaClassificacao($usuario, $temporada, 1000, 'sprint');

            if (count($retornoSprint['piloto_campeao']) > 0) {
                TituloSprint::create([
                    'temporada_id' => $temporada->id,
                    'pilotoEquipe_id' => $retornoSprint['piloto_campeao'][0]->pilotoEquipe_id,
                    'equipe_id' => $retornoSprint['equipe_campea'][0]->equipe_id,
                    'user_id' => $usuario,
                ]);
            }

            // MELHORIA 20261110 (playoff): campeão do mata-mata (só com a final completa)
            if ($temporada->temPlayoff()) {
                $playoff = $this->montaPlayoff($usuario, $temporada, 1000, $retorno['resultadoPilotos']);
                if ($playoff && $playoff['campeao']) {
                    $pilotoEquipe = \App\Models\Site\PilotoEquipe::find($playoff['campeao']['pilotoEquipe_id']);
                    TituloPlayoff::create([
                        'temporada_id' => $temporada->id,
                        'pilotoEquipe_id' => $playoff['campeao']['pilotoEquipe_id'],
                        'equipe_id' => $pilotoEquipe->equipe_id,
                        'vice_pilotoEquipe_id' => $playoff['vice']['pilotoEquipe_id'] ?? null,
                        'user_id' => $usuario,
                    ]);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $temporada = Temporada::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $temporada->delete();
        return redirect()->back();
    }

    /**
     * rotas para montar classificação de acordo com a corrida
     */

    public function getClassificacaoAposCorrida(Request $request)
    {

        $usuario = Auth::user()->id;
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $request->temporada_id)->first();
        $ordem = SqlInput::id($request->corrida_id);

        $retorno = $this->montaClassificacaoAposCorrida($usuario, $ordem, $temporada);
        $resultadosPilotos = $retorno['resultadoPilotos'];

        $corrida = Resultado::join('corridas', 'corridas.id', '=', 'resultados.corrida_id')
            ->where('resultados.user_id', Auth::user()->id)
            ->where('corridas.temporada_id', $temporada->id)
            ->where('resultados.chegada', '<>', null)
            ->where('corridas.ordem', $ordem)
            ->orderBy('corrida_id', 'desc')
            ->first();

        $corrida = Corrida::find($corrida->corrida_id);
        $ordemCorrida = $corrida->ordem;
        $corridaAtual = $corrida->evento?->des_nome ?? '-';

        // dd($resultadosPilotos);

        return response()->json([
            'message' => 'OK',
            'ordemCorrida' => $ordemCorrida,
            'corridaAtual' => $corridaAtual,
            'resultadosPilotos' => $resultadosPilotos
        ]);
    }

    public function montaClassificacaoAposCorrida($usuario, $ordem, $temporada)
    {
        // Values are concatenated into the SQL below: only integers get through.
        $temporadaId = (int) $temporada->id;
        $usuarioId = (int) $usuario;
        $ordemLimite = (int) $ordem;

        $cont = 1;
        $queryCountChegada = '';
        $queryCountOrderBy = '';

        while ($cont <= 43) {
            if ($cont == 43) {
                $queryCountChegada .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "N") AS posicao_' . $cont . '';
            } else {
                $queryCountChegada .= '(SELECT COUNT(*) FROM resultados AS r2 join corridas on (corridas.id = r2.corrida_id) WHERE r2.pilotoEquipe_id = piloto_equipes.id AND r2.chegada = ' . $cont . ' and corridas.flg_sprint = "N") AS posicao_' . $cont . ',';
            }
            $queryCountOrderBy .= ', posicao_' . $cont . ' desc';
            $cont++;
        }

        $resultadosPilotos = DB::select('
                                                            SELECT
                                                            piloto_id,
                                                            piloto_equipes.id AS pilotoEquipe_id,
                                                            pilotos.nome,
                                                            pilotos.sobrenome,
                                                            equipes.nome AS equipe,
                                                            equipes.imagem,
                                                            SUM(pontuacao) AS total,
                                                            ' . $queryCountChegada . '
                                                        FROM resultados
                                                        JOIN piloto_equipes ON piloto_equipes.id = resultados.pilotoEquipe_id
                                                        JOIN pilotos ON pilotos.id = piloto_equipes.piloto_id
                                                        JOIN equipes ON equipes.id = piloto_equipes.equipe_id
                                                        JOIN corridas ON corridas.id = resultados.corrida_id
                                                        JOIN temporadas ON temporadas.id = corridas.temporada_id
                                                        WHERE temporadas.id =  ' . $temporadaId . '
                                                        AND resultados.user_id =  ' . $usuarioId . '
                                                        and corridas.ordem <= ' . $ordemLimite . '
                                                        GROUP BY piloto_equipes.piloto_id, piloto_equipes.id, pilotos.nome, pilotos.sobrenome, equipes.nome, equipes.imagem
                                                        ORDER BY total DESC ' . $queryCountOrderBy . ';    
                                        ');

        $resultadosEquipes = DB::select('select equipe_id, equipes.nome as nome, equipes.imagem, sum(pontuacao) as total from resultados
                                        join piloto_equipes on piloto_equipes.id = resultados.pilotoEquipe_id
                                        join equipes on equipes.id = piloto_equipes.equipe_id
                                        join corridas on corridas.id = resultados.corrida_id
                                        join temporadas on temporadas.id = corridas.temporada_id
                                        where temporadas.id = ' . $temporadaId . '
                                        and resultados.user_id = ' . $usuarioId . '
                                        group by piloto_equipes.equipe_id
                                        order by total desc');

        return [
            'resultadoPilotos' => $resultadosPilotos,
            'resultadoEquipes' => $resultadosEquipes,
            'piloto_campeao' => $resultadosPilotos,
            'equipe_campea' => $resultadosEquipes
        ];
    }

    public function resultados(Request $request, $id)
    {
        // MELHORIA 20261110: ?tipo=sprint mostra só as sprints; padrão = só corridas principais (sprints não somam)
        $tipo = $request->query('tipo') === 'sprint' ? 'sprint' : 'principal';

        $usuario = Auth::user()->id;
        $temporada = Temporada::where('user_id', Auth::user()->id)->where('id', $id)->first();

        $totalCorridas = Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->where('flg_sprint', 'N')->count();
        // MELHORIA 20261105: a sprint tem a ordem da corrida principal do fim de semana; se a principal ainda
        // não foi cadastrada (ex.: 1N, 2S) a contagem acima fica 1 e o filtro "ordem <= 1" deixava a sprint de fora
        $totalCorridas = max($totalCorridas, (int) Corrida::where('user_id', Auth::user()->id)->where('temporada_id', $temporada->id)->max('ordem'));
        $corrida_ordem = $totalCorridas;

        $retorno = $this->montaClassificacao($usuario, $temporada, $corrida_ordem, $tipo);
        $resultadosPilotos = $retorno['resultadoPilotos'];

        $corridas = Corrida::where('temporada_id', $id)
            ->where('user_id', $usuario)
            // ->where('flg_sprint', 'N')
            ->when($tipo === 'sprint', function ($q) {
                $q->where('flg_sprint', 'S');
            }, function ($q) {
                $q->where(function ($w) {
                    $w->whereNull('flg_sprint')->orWhere('flg_sprint', '<>', 'S');
                });
            })
            ->orderBy('ordem', 'ASC')
            ->orderBy('flg_sprint', 'DESC')
            ->get();

        $temSprint = Corrida::where('temporada_id', $id)->where('user_id', $usuario)->where('flg_sprint', 'S')->exists();
        $porPontuacao = $request->route('porPontuacao');

        // MELHORIA 20261110 (layout): todos os resultados das corridas exibidas em uma consulta,
        // [corrida_id][piloto_id] => resultado. Mesma busca de PilotoEquipe::getResultadoPilotoEquipe (primeiro registro),
        // que antes rodava uma vez por célula da grade.
        $corridas = $corridas->filter(fn ($c) => isset($c->resultado))->values();
        $mapaResultados = [];
        DB::table('resultados')
            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
            ->whereIn('resultados.corrida_id', $corridas->pluck('id'))
            ->orderBy('resultados.id')
            ->get(['resultados.corrida_id', 'resultados.pilotoEquipe_id', 'resultados.largada', 'resultados.chegada',
                   'resultados.pontuacao', 'resultados.flg_abandono', 'piloto_equipes.piloto_id'])
            ->each(function ($r) use (&$mapaResultados) {
                if (!isset($mapaResultados[$r->corrida_id][$r->piloto_id])) {
                    $mapaResultados[$r->corrida_id][$r->piloto_id] = $r;
                }
            });

        return view('site.temporadas.resultados', compact('temporada', 'resultadosPilotos', 'corridas', 'tipo', 'temSprint', 'porPontuacao', 'mapaResultados'));
    }
}
