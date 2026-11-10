<?php

namespace App\Services;

use App\Http\Controllers\Site\TemporadaController;
use App\Models\Site\Equipe;
use App\Models\Site\Temporada;
use App\Support\ImageUrl;
use Illuminate\Support\Facades\DB;

/**
 * MELHORIA 20261110 (resumo da temporada): dados, estatísticas, curiosidades, "e se", fatos que não
 * aconteceram e manchetes de uma temporada.
 *
 * Só corridas principais entram nas estatísticas (sprints não somam pontos — ver memória do projeto);
 * as sprints aparecem apenas como título próprio e no cenário "se as sprints valessem pontos".
 * A classificação final é a oficial (TemporadaController::montaClassificacao, tipo 'principal').
 */
class TemporadaResumoService
{
    /** sistemas de pontuação alternativos para os cenários "e se" */
    private const SISTEMAS = [
        'classica' => ['nome' => 'Pontuação clássica', 'regra' => '10-6-4-3-2-1 (1991-2002)', 'pontos' => [10, 6, 4, 3, 2, 1]],
        'anos2000' => ['nome' => 'Pontuação dos anos 2000', 'regra' => '10-8-6-5-4-3-2-1 (2003-2009)', 'pontos' => [10, 8, 6, 5, 4, 3, 2, 1]],
        'vitorias' => ['nome' => 'Só vitórias contam', 'regra' => 'quem vence mais é campeão (desempate pelos 2ºs, 3ºs…)', 'pontos' => [1]],
    ];

    private TemporadaController $temporadas;

    private array $pilotos = [];
    private array $equipes = [];
    private array $corridas = [];      // só principais com resultado, na ordem
    private array $res = [];           // [corrida_idx][] = resultado
    private int $usuario;
    private Temporada $temporada;

    public function __construct()
    {
        $this->temporadas = new TemporadaController();
    }

    public function gerar(int $usuario, Temporada $temporada): array
    {
        $this->usuario = $usuario;
        $this->temporada = $temporada;
        $this->carregar();

        $base = [
            'temporada' => [
                'id' => $temporada->id,
                'nome' => $temporada->des_temporada,
                'referencia' => $temporada->referencia,
                'ano' => optional($temporada->ano)->ano,
                'finalizada' => $temporada->flg_finalizada === 'S',
                'observacoes' => trim((string) $temporada->observacoes),
            ],
            'vazia' => count($this->corridas) === 0,
        ];
        if ($base['vazia']) {
            return $base;
        }

        $oficial = $this->temporadas->montaClassificacao($usuario, $temporada, 1000, 'principal');
        $porPiloto = $this->estatisticasPilotos($oficial['resultadoPilotos']);
        $porEquipe = $this->estatisticasEquipes($oficial['resultadoEquipes']);
        $evolucao = $this->evolucao($porPiloto);
        $corridas = $this->corridaACorrida($evolucao);
        $duelos = $this->duelos();
        // duelos na ordem do campeonato de construtores
        usort($duelos, fn ($x, $y) => ($porEquipe[$x['equipe']['id']]['pos'] ?? 99) <=> ($porEquipe[$y['equipe']['id']]['pos'] ?? 99));
        $titulos = $this->titulos($porPiloto, $porEquipe);
        $fatos = $this->fatos($porPiloto, $porEquipe, $evolucao, $duelos, $titulos);
        $cenarios = $this->cenarios($porPiloto, $evolucao);

        return $base + [
            'kpis' => $this->kpis($porPiloto, $porEquipe),
            'titulos' => $titulos,
            'pilotos' => array_values($porPiloto),
            'equipes' => array_values($porEquipe),
            'evolucao' => $evolucao,
            'corridas' => $corridas,
            'duelos' => $duelos,
            'aconteceu' => $fatos['aconteceu'],
            'naoAconteceu' => $fatos['nao'],
            'cenarios' => $cenarios,
            'manchetes' => $this->manchetes($porPiloto, $porEquipe, $evolucao, $duelos, $titulos, $fatos['dados']),
        ];
    }

    // =====================================================================================
    // CARGA
    // =====================================================================================

    private function carregar(): void
    {
        $condicoes = DB::table('condicao_climaticas')->pluck('descricao', 'id');

        $lista = \App\Models\Site\Corrida::with(['evento', 'pista.pais'])
            ->where('user_id', $this->usuario)
            ->where('temporada_id', $this->temporada->id)
            ->where(fn ($q) => $q->whereNull('flg_sprint')->orWhere('flg_sprint', '<>', 'S'))
            ->orderBy('ordem')->orderBy('id')
            ->get();
        $this->totalPrevisto = max($lista->count(), (int) $this->temporada->qtd_corridas);

        $linhas = DB::table('resultados as r')
            ->join('piloto_equipes as pe', 'pe.id', '=', 'r.pilotoEquipe_id')
            ->whereIn('r.corrida_id', $lista->pluck('id'))
            ->where('r.user_id', $this->usuario)
            ->whereNotNull('r.chegada')->where('r.chegada', '>', 0)
            ->where('pe.piloto_id', '<>', 0)
            ->orderBy('r.chegada')
            ->get(['r.corrida_id', 'r.pilotoEquipe_id as pe', 'r.largada', 'r.chegada', 'r.pontuacao', 'r.flg_abandono', 'pe.piloto_id', 'pe.equipe_id'])
            ->groupBy('corrida_id');

        $idsPilotos = $idsEquipes = [];
        foreach ($lista as $c) {
            if (!isset($linhas[$c->id])) {
                continue;
            }
            $idx = count($this->corridas);
            $this->corridas[] = [
                'id' => $c->id,
                'ordem' => (int) $c->ordem,
                'evento' => optional($c->evento)->des_nome ?? optional($c->pista)->nome,
                'pista' => optional($c->pista)->nome,
                'bandeira' => optional(optional($c->pista)->pais)->imagem ? ImageUrl::for($c->pista->pais->imagem) : null,
                'clima' => $c->condicao_id ? ($condicoes[$c->condicao_id] ?? null) : null,
                'safety' => $c->qtd_safety_car !== null ? (int) $c->qtd_safety_car : null,
                'volta_rapida' => $c->volta_rapida ? (int) $c->volta_rapida : null,
                'url' => route('resultados.show', [$c->id]),
            ];
            $vistos = [];
            foreach ($linhas[$c->id] as $l) {
                if (isset($vistos[$l->piloto_id])) {
                    continue; // mesmo critério da tela de resultados: primeiro registro do piloto
                }
                $vistos[$l->piloto_id] = true;
                $this->res[$idx][] = [
                    'pe' => (int) $l->pe,
                    'piloto' => (int) $l->piloto_id,
                    'equipe' => (int) $l->equipe_id,
                    'largada' => (int) $l->largada,
                    'chegada' => (int) $l->chegada,
                    'pontos' => (float) $l->pontuacao,
                    'abandono' => $l->flg_abandono === 'S',
                    'vr' => $this->corridas[$idx]['volta_rapida'] === (int) $l->pe,
                ];
                $idsPilotos[$l->piloto_id] = true;
                $idsEquipes[$l->equipe_id] = true;
            }
        }

        $paises = DB::table('paises')->pluck('imagem', 'id');
        foreach (DB::table('pilotos')->whereIn('id', array_keys($idsPilotos))->get(['id', 'nome', 'sobrenome', 'imagem', 'pais_id']) as $p) {
            $this->pilotos[$p->id] = [
                'id' => (int) $p->id,
                'nome' => trim($p->nome . ' ' . $p->sobrenome),
                'sobrenome' => $p->sobrenome ?: $p->nome,
                'img' => ImageUrl::for($p->imagem),
                'bandeira' => !empty($paises[$p->pais_id]) ? ImageUrl::for($paises[$p->pais_id]) : null,
                'url' => route('pilotos.show', [$p->id]),
            ];
        }
        foreach (DB::table('equipes')->whereIn('id', array_keys($idsEquipes))->get(['id', 'nome', 'imagem', 'des_cor']) as $e) {
            $this->equipes[$e->id] = [
                'id' => (int) $e->id,
                'nome' => $e->nome,
                'img' => ImageUrl::for($e->imagem),
                'cor' => $e->des_cor ?: '#adb5bd',
                'fundo' => Equipe::corFundoIcone($e->des_cor, $e->imagem),
                'url' => route('equipes.show', [$e->id]),
            ];
        }
    }

    private int $totalPrevisto = 0;

    private function p(int $id): array
    {
        return $this->pilotos[$id] ?? ['id' => $id, 'nome' => 'Piloto #' . $id, 'sobrenome' => '#' . $id, 'img' => ImageUrl::PLACEHOLDER, 'bandeira' => null, 'url' => '#'];
    }

    private function e(int $id): array
    {
        return $this->equipes[$id] ?? ['id' => $id, 'nome' => 'Equipe #' . $id, 'img' => ImageUrl::PLACEHOLDER, 'cor' => '#adb5bd', 'fundo' => '#adb5bd', 'url' => '#'];
    }

    private function etapa(int $idx): string
    {
        $c = $this->corridas[$idx];
        return $c['ordem'] . 'ª etapa (' . $c['evento'] . ')';
    }

    private static function qtd($n, string $singular, string $plural): string
    {
        return $n . ' ' . ($n == 1 ? $singular : $plural);
    }

    private static function num($v): string
    {
        return floor($v) == $v ? number_format($v, 0, ',', '.') : number_format($v, 1, ',', '.');
    }

    private static function pct($parte, $total): string
    {
        return $total ? number_format($parte * 100 / $total, 0, ',', '.') . '%' : '0%';
    }

    // =====================================================================================
    // ESTATÍSTICAS
    // =====================================================================================

    private function estatisticasPilotos(array $oficial): array
    {
        $st = [];
        foreach ($oficial as $i => $linha) {
            $id = (int) $linha->piloto_id;
            $st[$id] = $this->p($id) + [
                'pos' => $i + 1, 'pontos' => (float) $linha->total, 'equipe' => null, 'equipes' => [],
                'corridas' => 0, 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'top10' => 0, 'vr' => 0, 'abandonos' => 0,
                'pontuou' => 0, 'somaLargada' => 0, 'nLargada' => 0, 'somaChegada' => 0, 'ganhos' => 0, 'melhor' => null, 'pior' => null,
                'hattricks' => 0, 'seqVitorias' => 0, 'seqPodios' => 0, 'seqPontos' => 0,
                'chegadas' => [], 'pontosCorrida' => [],
            ];
        }

        foreach ($this->corridas as $idx => $c) {
            foreach ($this->res[$idx] as $r) {
                if (!isset($st[$r['piloto']])) {
                    continue;
                }
                $s = &$st[$r['piloto']];
                $s['corridas']++;
                $s['equipes'][$r['equipe']] = $r['equipe'];
                $s['equipe'] = $r['equipe']; // última equipe
                $vit = $r['chegada'] === 1;
                $s['vitorias'] += $vit;
                $s['poles'] += $r['largada'] === 1;
                $s['podios'] += $r['chegada'] <= 3;
                $s['top10'] += $r['chegada'] <= 10;
                $s['vr'] += $r['vr'];
                $s['abandonos'] += $r['abandono'];
                $s['pontuou'] += $r['pontos'] > 0;
                if ($r['largada'] > 0) {
                    $s['somaLargada'] += $r['largada'];
                    $s['nLargada']++;
                    if (!$r['abandono']) {
                        $s['ganhos'] += $r['largada'] - $r['chegada'];
                    }
                }
                $s['somaChegada'] += $r['chegada'];
                if (!$r['abandono']) {
                    $s['melhor'] = $s['melhor'] === null ? $r['chegada'] : min($s['melhor'], $r['chegada']);
                }
                $s['pior'] = $s['pior'] === null ? $r['chegada'] : max($s['pior'], $r['chegada']);
                if ($vit && $r['largada'] === 1 && $r['vr']) {
                    $s['hattricks']++;
                }
                $s['chegadas'][$idx] = $r;
                $s['pontosCorrida'][$idx] = $r['pontos'];
                unset($s);
            }
        }

        foreach ($st as &$s) {
            $s['mediaLargada'] = $s['nLargada'] ? round($s['somaLargada'] / $s['nLargada'], 1) : null;
            $s['mediaChegada'] = $s['corridas'] ? round($s['somaChegada'] / $s['corridas'], 1) : null;
            // sequências (corridas seguidas, entre as que o piloto disputou)
            foreach (['seqVitorias' => fn ($r) => $r['chegada'] === 1, 'seqPodios' => fn ($r) => $r['chegada'] <= 3, 'seqPontos' => fn ($r) => $r['pontos'] > 0] as $campo => $ok) {
                $atual = $max = 0;
                $ini = $fimMax = $iniMax = null;
                foreach ($s['chegadas'] as $idx => $r) {
                    if ($ok($r)) {
                        if ($atual === 0) {
                            $ini = $idx;
                        }
                        $atual++;
                        if ($atual > $max) {
                            $max = $atual;
                            $iniMax = $ini;
                            $fimMax = $idx;
                        }
                    } else {
                        $atual = 0;
                    }
                }
                $s[$campo] = $max;
                $s[$campo . 'Periodo'] = $max ? [$iniMax, $fimMax] : null;
            }
            $s['equipeInfo'] = $s['equipe'] ? $this->e($s['equipe']) : null;
        }
        unset($s);

        return $st;
    }

    private function estatisticasEquipes(array $oficial): array
    {
        $st = [];
        foreach ($oficial as $i => $linha) {
            $id = (int) $linha->equipe_id;
            $st[$id] = $this->e($id) + ['pos' => $i + 1, 'pontos' => (float) $linha->total, 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'dobradinhas' => 0, 'abandonos' => 0, 'pilotos' => [], 'listaDobradinhas' => []];
        }
        foreach ($this->corridas as $idx => $c) {
            $porChegada = [];
            foreach ($this->res[$idx] as $r) {
                if (!isset($st[$r['equipe']])) {
                    continue;
                }
                $s = &$st[$r['equipe']];
                $s['vitorias'] += $r['chegada'] === 1;
                $s['poles'] += $r['largada'] === 1;
                $s['podios'] += $r['chegada'] <= 3;
                $s['abandonos'] += $r['abandono'];
                $s['pilotos'][$r['piloto']] = $r['piloto'];
                $porChegada[$r['chegada']] = $r;
                unset($s);
            }
            if (isset($porChegada[1], $porChegada[2]) && $porChegada[1]['equipe'] === $porChegada[2]['equipe'] && isset($st[$porChegada[1]['equipe']])) {
                $st[$porChegada[1]['equipe']]['dobradinhas']++;
                $st[$porChegada[1]['equipe']]['listaDobradinhas'][] = $idx;
            }
        }
        foreach ($st as &$s) {
            $s['pilotos'] = array_map(fn ($id) => $this->p($id)['sobrenome'], array_values($s['pilotos']));
        }
        unset($s);

        return $st;
    }

    /** classificação acumulada após cada corrida + líder de cada etapa + gráfico dos 6 primeiros */
    private function evolucao(array $porPiloto): array
    {
        $acum = [];
        $contagem = [];
        $lideres = [];
        $serie = [];
        $top = array_slice(array_keys($porPiloto), 0, 6);
        $maxPorCorrida = 0;
        $posicoesApos = [];
        $pontosApos = [];

        foreach ($this->corridas as $idx => $c) {
            foreach ($this->res[$idx] as $r) {
                $acum[$r['piloto']] = ($acum[$r['piloto']] ?? 0) + $r['pontos'];
                $contagem[$r['piloto']][$r['chegada']] = ($contagem[$r['piloto']][$r['chegada']] ?? 0) + 1;
                $maxPorCorrida = max($maxPorCorrida, $r['pontos']);
            }
            $ordem = $this->ordenar($acum, $contagem);
            $lider = $ordem[0];
            $lideres[$idx] = ['piloto' => $lider, 'pontos' => $acum[$lider], 'vantagem' => isset($ordem[1]) ? $acum[$lider] - $acum[$ordem[1]] : $acum[$lider], 'segundo' => $ordem[1] ?? null];
            $posicoesApos[$idx] = array_flip($ordem);
            $pontosApos[$idx] = $acum;
            foreach ($top as $id) {
                $serie[$id][] = $acum[$id] ?? 0;
            }
        }

        // trocas de liderança e quem liderou mais etapas
        $trocas = 0;
        $etapasLider = [];
        $anterior = null;
        foreach ($lideres as $l) {
            if ($anterior !== null && $anterior !== $l['piloto']) {
                $trocas++;
            }
            $anterior = $l['piloto'];
            $etapasLider[$l['piloto']] = ($etapasLider[$l['piloto']] ?? 0) + 1;
        }
        arsort($etapasLider);

        // título matematicamente garantido: vantagem maior que os pontos ainda em jogo
        $campeao = array_key_first($porPiloto);
        $garantido = null;
        $n = count($this->corridas);
        foreach ($lideres as $idx => $l) {
            $restantes = $this->totalPrevisto - ($idx + 1);
            if ($l['piloto'] === $campeao && $l['segundo'] !== null && $l['vantagem'] > $restantes * $maxPorCorrida) {
                $garantido = ['idx' => $idx, 'restantes' => $restantes];
                break;
            }
        }
        // a partir de quando o campeão liderou sem perder mais a ponta
        $desde = null;
        for ($i = $n - 1; $i >= 0; $i--) {
            if ($lideres[$i]['piloto'] !== $campeao) {
                break;
            }
            $desde = $i;
        }

        $cores = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300']; // paleta categórica de referência (slots 1-6)
        $grafico = [
            'rotulos' => array_map(fn ($c) => $c['ordem'] . 'ª', $this->corridas),
            'eventos' => array_map(fn ($c) => $c['evento'], $this->corridas),
            'series' => array_values(array_map(fn ($id, $i) => ['nome' => $this->p($id)['nome'], 'cor' => $cores[$i], 'dados' => $serie[$id] ?? []], $top, array_keys($top))),
        ];

        return [
            'lideres' => array_map(fn ($l, $idx) => [
                'piloto' => $this->p($l['piloto']), 'pontos' => $l['pontos'], 'vantagem' => $l['vantagem'],
                'cor' => isset($porPiloto[$l['piloto']]['equipeInfo']) ? $porPiloto[$l['piloto']]['equipeInfo']['cor'] : '#adb5bd',
                'etapa' => $this->corridas[$idx]['ordem'], 'evento' => $this->corridas[$idx]['evento'],
            ], $lideres, array_keys($lideres)),
            'idLideres' => array_map(fn ($l) => $l['piloto'], $lideres),
            'trocas' => $trocas,
            'qtdLideres' => count($etapasLider),
            'etapasLider' => $etapasLider,
            'garantido' => $garantido,
            'liderDesde' => $desde,
            'maxPorCorrida' => $maxPorCorrida,
            'grafico' => $grafico,
            'posicoesApos' => $posicoesApos,
            'pontosApos' => $pontosApos,
            'metade' => (int) ceil($n / 2) - 1,
        ];
    }

    /** ordena por pontos e desempata pelas posições (1ºs, 2ºs, ...), como a classificação oficial */
    private function ordenar(array $pontos, array $contagem): array
    {
        $ids = array_keys($pontos);
        usort($ids, function ($a, $b) use ($pontos, $contagem) {
            if ($pontos[$a] != $pontos[$b]) {
                return $pontos[$b] <=> $pontos[$a];
            }
            for ($pos = 1; $pos <= 43; $pos++) {
                $ca = $contagem[$a][$pos] ?? 0;
                $cb = $contagem[$b][$pos] ?? 0;
                if ($ca !== $cb) {
                    return $cb <=> $ca;
                }
            }
            return $a <=> $b;
        });
        return $ids;
    }

    private function corridaACorrida(array $evolucao): array
    {
        $linhas = [];
        foreach ($this->corridas as $idx => $c) {
            $porChegada = [];
            $pole = null;
            $vr = null;
            $abandonos = 0;
            foreach ($this->res[$idx] as $r) {
                $porChegada[$r['chegada']] = $r;
                if ($r['largada'] === 1) {
                    $pole = $r;
                }
                if ($r['vr']) {
                    $vr = $r;
                }
                $abandonos += $r['abandono'];
            }
            $fmt = fn ($r) => $r ? ['piloto' => $this->p($r['piloto']), 'equipe' => $this->e($r['equipe']), 'largada' => $r['largada'], 'chegada' => $r['chegada']] : null;
            $linhas[] = $c + [
                'pole' => $fmt($pole),
                'podio' => [$fmt($porChegada[1] ?? null), $fmt($porChegada[2] ?? null), $fmt($porChegada[3] ?? null)],
                'vrPiloto' => $fmt($vr),
                'abandonos' => $abandonos,
                'lider' => $evolucao['lideres'][$idx],
                'trocaLider' => $idx > 0 && $evolucao['idLideres'][$idx] !== $evolucao['idLideres'][$idx - 1],
            ];
        }
        return $linhas;
    }

    /** companheiros de equipe: quem largou e chegou à frente nas corridas que fizeram juntos */
    private function duelos(): array
    {
        $porEquipe = [];
        foreach ($this->corridas as $idx => $c) {
            foreach ($this->res[$idx] as $r) {
                $porEquipe[$r['equipe']][$idx][$r['piloto']] = $r;
            }
        }

        $duelos = [];
        foreach ($porEquipe as $equipe => $porCorrida) {
            $freq = [];
            foreach ($porCorrida as $rs) {
                foreach ($rs as $p => $r) {
                    $freq[$p] = ($freq[$p] ?? 0) + 1;
                }
            }
            arsort($freq);
            $par = array_slice(array_keys($freq), 0, 2);
            if (count($par) < 2) {
                continue;
            }
            [$a, $b] = $par;
            $d = ['juntos' => 0, 'quali' => [0, 0], 'corrida' => [0, 0], 'pontos' => [0, 0]];
            foreach ($porCorrida as $rs) {
                if (!isset($rs[$a], $rs[$b])) {
                    continue;
                }
                $d['juntos']++;
                if ($rs[$a]['largada'] > 0 && $rs[$b]['largada'] > 0) {
                    $d['quali'][$rs[$a]['largada'] < $rs[$b]['largada'] ? 0 : 1]++;
                }
                $d['corrida'][$rs[$a]['chegada'] < $rs[$b]['chegada'] ? 0 : 1]++;
                $d['pontos'][0] += $rs[$a]['pontos'];
                $d['pontos'][1] += $rs[$b]['pontos'];
            }
            if ($d['juntos'] < 2) {
                continue;
            }
            // quem venceu mais corridas fica à esquerda
            if ($d['corrida'][1] > $d['corrida'][0] || ($d['corrida'][1] === $d['corrida'][0] && $d['pontos'][1] > $d['pontos'][0])) {
                [$a, $b] = [$b, $a];
                $d['quali'] = array_reverse($d['quali']);
                $d['corrida'] = array_reverse($d['corrida']);
                $d['pontos'] = array_reverse($d['pontos']);
            }
            $duelos[] = $d + ['equipe' => $this->e($equipe), 'a' => $this->p($a), 'b' => $this->p($b),
                'diferenca' => abs($d['corrida'][0] - $d['corrida'][1])];
        }
        usort($duelos, fn ($x, $y) => [$y['juntos'], $x['equipe']['nome']] <=> [$x['juntos'], $y['equipe']['nome']]);

        return $duelos;
    }

    private function titulos(array $porPiloto, array $porEquipe): array
    {
        $pil = array_values($porPiloto);
        $eq = array_values($porEquipe);
        $t = [
            'piloto' => $pil[0] ?? null,
            'vice' => $pil[1] ?? null,
            'terceiro' => $pil[2] ?? null,
            'equipe' => $eq[0] ?? null,
            'equipeVice' => $eq[1] ?? null,
            'equipeTerceira' => $eq[2] ?? null,
            'margem' => isset($pil[1]) ? $pil[0]['pontos'] - $pil[1]['pontos'] : null,
            'margemEquipes' => isset($eq[1]) ? $eq[0]['pontos'] - $eq[1]['pontos'] : null,
            'sprint' => null,
            'playoff' => null,
            'qtdSprints' => 0,
        ];

        // sprints: classificação própria (não soma pontos no campeonato)
        $t['qtdSprints'] = \App\Models\Site\Corrida::where('user_id', $this->usuario)->where('temporada_id', $this->temporada->id)
            ->where('flg_sprint', 'S')->whereHas('resultado')->count();
        if ($t['qtdSprints'] > 0) {
            $sp = $this->temporadas->montaClassificacao($this->usuario, $this->temporada, 1000, 'sprint')['resultadoPilotos'];
            if ($sp) {
                $t['sprint'] = $this->p((int) $sp[0]->piloto_id) + ['pontos' => (float) $sp[0]->total];
            }
        }

        // playoff
        if ($this->temporada->temPlayoff()) {
            $po = $this->temporadas->montaPlayoff($this->usuario, $this->temporada, 1000);
            if ($po) {
                $t['playoff'] = [
                    'fase' => $po['fase_atual'],
                    'campeao' => $po['campeao'] ? $this->p($po['campeao']['piloto_id']) : null,
                    'vice' => $po['vice'] ? $this->p($po['vice']['piloto_id']) : null,
                    'link' => route('temporadas.classificacao', [$this->temporada->id]) . '?aba=playoff',
                ];
            }
        }

        return $t;
    }

    private function kpis(array $porPiloto, array $porEquipe): array
    {
        $vencedores = count(array_filter($porPiloto, fn ($p) => $p['vitorias'] > 0));
        $poles = count(array_filter($porPiloto, fn ($p) => $p['poles'] > 0));
        $abandonos = array_sum(array_column($porPiloto, 'abandonos'));
        $dobradinhas = array_sum(array_column($porEquipe, 'dobradinhas'));
        $safety = array_sum(array_map(fn ($c) => (int) $c['safety'], $this->corridas));
        $comSafety = count(array_filter($this->corridas, fn ($c) => $c['safety'] !== null));
        $n = count($this->corridas);

        $poleVenceu = 0;
        foreach ($this->corridas as $idx => $c) {
            foreach ($this->res[$idx] as $r) {
                if ($r['largada'] === 1 && $r['chegada'] === 1) {
                    $poleVenceu++;
                }
            }
        }

        return [
            ['icone' => 'bi-flag-fill', 'rotulo' => 'Corridas', 'valor' => $n, 'detalhe' => $this->totalPrevisto > $n ? 'de ' . $this->totalPrevisto . ' previstas' : 'disputadas'],
            ['icone' => 'bi-trophy-fill', 'rotulo' => 'Vencedores', 'valor' => $vencedores, 'detalhe' => self::qtd($poles, 'pole diferente', 'poles diferentes')],
            ['icone' => 'bi-bullseye', 'rotulo' => 'Pole virou vitória', 'valor' => self::pct($poleVenceu, $n), 'detalhe' => $poleVenceu . ' de ' . $n . ' corridas'],
            ['icone' => 'bi-people-fill', 'rotulo' => 'Dobradinhas', 'valor' => $dobradinhas, 'detalhe' => $dobradinhas ? 'mais: ' . $this->melhor($porEquipe, 'dobradinhas')['nome'] : 'nenhuma equipe fez 1-2'],
            ['icone' => 'bi-exclamation-triangle-fill', 'rotulo' => 'Abandonos', 'valor' => $abandonos, 'detalhe' => $safety > 0 ? self::qtd($safety, 'safety car', 'safety cars') . ' em ' . self::qtd($comSafety, 'corrida', 'corridas') : 'média de ' . number_format($n ? $abandonos / $n : 0, 1, ',', '.') . ' por corrida'],
        ];
    }

    // =====================================================================================
    // FATOS (aconteceu / não aconteceu)
    // =====================================================================================

    private function fatos(array $porPiloto, array $porEquipe, array $evolucao, array $duelos, array $titulos): array
    {
        $n = count($this->corridas);
        $campeao = $titulos['piloto'];
        $sim = [];
        $nao = [];
        $dados = [];

        // vitória de mais longe e maior escalada / queda
        $vitDeLonge = null;
        $escalada = null;
        $queda = null;
        $maisAbandonos = null;
        $hattricks = [];
        foreach ($this->corridas as $idx => $c) {
            $ab = 0;
            foreach ($this->res[$idx] as $r) {
                $ab += $r['abandono'];
                if ($r['largada'] <= 0) {
                    continue;
                }
                $ganho = $r['largada'] - $r['chegada'];
                if ($r['chegada'] === 1 && (!$vitDeLonge || $r['largada'] > $vitDeLonge['r']['largada'])) {
                    $vitDeLonge = ['r' => $r, 'idx' => $idx];
                }
                if (!$r['abandono'] && (!$escalada || $ganho > $escalada['ganho'])) {
                    $escalada = ['r' => $r, 'idx' => $idx, 'ganho' => $ganho];
                }
                if (!$r['abandono'] && (!$queda || $ganho < $queda['ganho'])) {
                    $queda = ['r' => $r, 'idx' => $idx, 'ganho' => $ganho];
                }
                if ($r['chegada'] === 1 && $r['largada'] === 1 && $r['vr']) {
                    $hattricks[] = ['piloto' => $r['piloto'], 'idx' => $idx];
                }
            }
            if (!$maisAbandonos || $ab > $maisAbandonos['qtd']) {
                $maisAbandonos = ['idx' => $idx, 'qtd' => $ab];
            }
        }
        $dados += compact('vitDeLonge', 'escalada', 'queda', 'maisAbandonos', 'hattricks');

        // --- ACONTECEU ---
        $vencedores = array_filter($porPiloto, fn ($p) => $p['vitorias'] > 0);
        uasort($vencedores, fn ($a, $b) => $b['vitorias'] <=> $a['vitorias']);
        $maior = reset($vencedores) ?: null;
        if ($maior) $sim[] = ['icone' => 'bi-trophy', 'titulo' => self::qtd(count($vencedores), 'vencedor', 'vencedores diferentes'),
            'texto' => count($vencedores) === 1
                ? $maior['nome'] . ' venceu todas as ' . $n . ' corridas da temporada.'
                : $maior['nome'] . ' foi quem mais venceu (' . self::qtd($maior['vitorias'], 'vitória', 'vitórias') . ', ' . self::pct($maior['vitorias'], $n) . ' das corridas).'];

        if ($vitDeLonge && $vitDeLonge['r']['largada'] > 1) {
            $sim[] = ['icone' => 'bi-lightning-charge-fill', 'titulo' => 'Vitória largando de P' . $vitDeLonge['r']['largada'],
                'texto' => $this->p($vitDeLonge['r']['piloto'])['nome'] . ' venceu a ' . $this->etapa($vitDeLonge['idx']) . ' saindo da ' . $vitDeLonge['r']['largada'] . 'ª posição do grid.'];
        }
        if ($escalada && $escalada['ganho'] > 0) {
            $sim[] = ['icone' => 'bi-graph-up-arrow', 'titulo' => '+' . $escalada['ganho'] . ' posições numa corrida',
                'texto' => $this->p($escalada['r']['piloto'])['nome'] . ' largou em ' . $escalada['r']['largada'] . 'º e terminou em ' . $escalada['r']['chegada'] . 'º na ' . $this->etapa($escalada['idx']) . '.'];
        }
        if ($queda && $queda['ganho'] < 0) {
            $sim[] = ['icone' => 'bi-graph-down-arrow', 'titulo' => $queda['ganho'] . ' posições: a maior queda',
                'texto' => $this->p($queda['r']['piloto'])['nome'] . ' largou em ' . $queda['r']['largada'] . 'º e cruzou a linha em ' . $queda['r']['chegada'] . 'º na ' . $this->etapa($queda['idx']) . ' (sem abandonar).'];
        }
        if ($hattricks) {
            $porQuem = array_count_values(array_column($hattricks, 'piloto'));
            arsort($porQuem);
            $sim[] = ['icone' => 'bi-stars', 'titulo' => self::qtd(count($hattricks), 'hat-trick', 'hat-tricks'),
                'texto' => 'Pole, vitória e volta mais rápida na mesma corrida: ' . implode(', ', array_map(fn ($id, $q) => $this->p($id)['sobrenome'] . ($q > 1 ? ' (' . $q . 'x)' : ''), array_keys($porQuem), $porQuem)) . '.'];
        }
        $seqVit = $this->melhor($porPiloto, 'seqVitorias');
        if ($seqVit && $seqVit['seqVitorias'] >= 2) {
            [$i, $f] = $seqVit['seqVitoriasPeriodo'];
            $sim[] = ['icone' => 'bi-fire', 'titulo' => self::qtd($seqVit['seqVitorias'], 'vitória seguida', 'vitórias seguidas'),
                'texto' => $seqVit['nome'] . ' emendou a sequência entre a ' . $this->corridas[$i]['ordem'] . 'ª e a ' . $this->corridas[$f]['ordem'] . 'ª etapa.'];
        }
        $dob = array_filter($porEquipe, fn ($e) => $e['dobradinhas'] > 0);
        if ($dob) {
            uasort($dob, fn ($a, $b) => $b['dobradinhas'] <=> $a['dobradinhas']);
            $sim[] = ['icone' => 'bi-people', 'titulo' => self::qtd(array_sum(array_column($dob, 'dobradinhas')), 'dobradinha', 'dobradinhas'),
                'texto' => implode(', ', array_map(fn ($e) => $e['nome'] . ' (' . $e['dobradinhas'] . ')', $dob)) . '.'];
        }
        $sempre = array_filter($porPiloto, fn ($p) => $p['corridas'] === $n && $p['pontuou'] === $n);
        if ($sempre && $n > 1) {
            $sim[] = ['icone' => 'bi-check2-circle', 'titulo' => 'Pontuou em todas',
                'texto' => implode(', ', array_map(fn ($p) => $p['nome'], $sempre)) . (count($sempre) > 1 ? ' marcaram' : ' marcou') . ' pontos nas ' . $n . ' corridas.'];
        }
        if ($maisAbandonos && $maisAbandonos['qtd'] > 0) {
            $sim[] = ['icone' => 'bi-cone-striped', 'titulo' => self::qtd($maisAbandonos['qtd'], 'abandono', 'abandonos') . ' numa corrida',
                'texto' => 'A ' . $this->etapa($maisAbandonos['idx']) . ' foi a corrida mais caótica do ano.'];
        }
        $maisAb = $this->melhor($porPiloto, 'abandonos');
        if ($maisAb && $maisAb['abandonos'] >= 2) {
            $sim[] = ['icone' => 'bi-wrench-adjustable', 'titulo' => 'Azarado do ano',
                'texto' => $maisAb['nome'] . ' abandonou ' . $maisAb['abandonos'] . ' das ' . $maisAb['corridas'] . ' corridas que disputou.'];
        }
        $ganhos = $this->melhor($porPiloto, 'ganhos');
        if ($ganhos && $ganhos['ganhos'] > 0) {
            $sim[] = ['icone' => 'bi-arrow-up-right-circle', 'titulo' => 'Rei das ultrapassagens',
                'texto' => $ganhos['nome'] . ' ganhou ' . $ganhos['ganhos'] . ' posições somando todas as corridas (largada x chegada).'];
        }
        if ($evolucao['qtdLideres'] > 1) {
            $sim[] = ['icone' => 'bi-shuffle', 'titulo' => self::qtd($evolucao['qtdLideres'], 'líder diferente', 'líderes diferentes'),
                'texto' => 'A ponta do campeonato trocou de mãos ' . self::qtd($evolucao['trocas'], 'vez', 'vezes') . ' ao longo da temporada.'];
        }
        $clima = array_count_values(array_filter(array_column($this->corridas, 'clima')));
        $chuva = [];
        foreach ($this->corridas as $idx => $c) {
            if ($c['clima'] && preg_match('/chuv|molhad|wet|garoa/i', $c['clima'])) {
                foreach ($this->res[$idx] as $r) {
                    if ($r['chegada'] === 1) {
                        $chuva[] = $this->p($r['piloto'])['sobrenome'] . ' (' . $c['evento'] . ')';
                    }
                }
            }
        }
        if ($chuva) {
            $sim[] = ['icone' => 'bi-cloud-rain-heavy', 'titulo' => self::qtd(count($chuva), 'corrida com chuva', 'corridas com chuva'), 'texto' => 'Vencedores no molhado: ' . implode(', ', $chuva) . '.'];
        }
        if ($campeao) {
            $textoCampeao = $campeao['nome'] . ' terminou no pódio em ' . $campeao['podios'] . ' de ' . $campeao['corridas'] . ' corridas';
            $textoCampeao .= $campeao['pior'] !== null ? '; o pior resultado foi um ' . $campeao['pior'] . 'º lugar.' : '.';
            $sim[] = ['icone' => 'bi-award', 'titulo' => 'A campanha do campeão', 'texto' => $textoCampeao];
            if ($maior && $maior['id'] !== $campeao['id'] && $maior['vitorias'] > $campeao['vitorias']) {
                $sim[] = ['icone' => 'bi-speedometer2', 'titulo' => 'Regularidade venceu',
                    'texto' => $campeao['nome'] . ' foi campeão com ' . self::qtd($campeao['vitorias'], 'vitória', 'vitórias') . ', menos que as ' . $maior['vitorias'] . ' de ' . $maior['nome'] . '.'];
            }
        }
        if ($titulos['equipe'] && $campeao && $campeao['equipe'] && $titulos['equipe']['id'] !== $campeao['equipe']) {
            $sim[] = ['icone' => 'bi-diagram-2', 'titulo' => 'Títulos divididos',
                'texto' => 'O campeão de pilotos (' . $campeao['equipeInfo']['nome'] . ') não é da equipe campeã de construtores (' . $titulos['equipe']['nome'] . ').'];
        }

        // --- NÃO ACONTECEU ---
        if (!$hattricks) {
            $nao[] = ['icone' => 'bi-stars', 'titulo' => 'Nenhum hat-trick', 'texto' => 'Ninguém conseguiu pole, vitória e volta mais rápida na mesma corrida.'];
        }
        if (!$dob) {
            $nao[] = ['icone' => 'bi-people', 'titulo' => 'Nenhuma dobradinha', 'texto' => 'Nenhuma equipe colocou os dois carros em 1º e 2º.'];
        }
        if ($seqVit && $seqVit['seqVitorias'] < 2 && $n > 1) {
            $nao[] = ['icone' => 'bi-fire', 'titulo' => 'Ninguém venceu duas seguidas', 'texto' => 'Nenhum piloto repetiu a vitória na corrida seguinte.'];
        }
        if ($vitDeLonge && $vitDeLonge['r']['largada'] <= 10) {
            $nao[] = ['icone' => 'bi-arrow-left-right', 'titulo' => 'Nenhuma vitória de fora do top 10', 'texto' => 'Todo vencedor largou entre os 10 primeiros (o mais distante saiu de P' . $vitDeLonge['r']['largada'] . ').'];
        }
        if (!$sempre && $n > 1) {
            $nao[] = ['icone' => 'bi-check2-circle', 'titulo' => 'Ninguém pontuou em todas', 'texto' => 'Todos os pilotos passaram ao menos uma corrida sem marcar pontos.'];
        }
        if ($campeao && $campeao['abandonos'] === 0) {
            $nao[] = ['icone' => 'bi-shield-check', 'titulo' => 'O campeão não abandonou', 'texto' => $campeao['nome'] . ' completou todas as ' . $campeao['corridas'] . ' corridas que disputou.'];
        }
        if ($evolucao['qtdLideres'] === 1 && $n > 1) {
            $nao[] = ['icone' => 'bi-shuffle', 'titulo' => 'A liderança nunca mudou', 'texto' => $this->p($evolucao['idLideres'][0])['nome'] . ' liderou de ponta a ponta.'];
        }
        foreach (array_slice($porPiloto, 0, 5, true) as $p) {
            if ($p['vitorias'] === 0) {
                $nao[] = ['icone' => 'bi-emoji-neutral', 'titulo' => $p['pos'] . 'º sem vencer', 'texto' => $p['nome'] . ' terminou em ' . $p['pos'] . 'º no campeonato sem nenhuma vitória' . ($p['melhor'] ? ' (melhor resultado: ' . $p['melhor'] . 'º).' : '.')];
            }
        }
        foreach (array_slice($porPiloto, 0, 10, true) as $p) {
            if ($p['podios'] === 0 && $p['corridas'] > 0) {
                $nao[] = ['icone' => 'bi-bar-chart-steps', 'titulo' => 'Top 10 sem pódio', 'texto' => $p['nome'] . ' foi o ' . $p['pos'] . 'º do campeonato e nunca subiu ao pódio.'];
                break;
            }
        }
        $semPole = array_filter(array_slice($porPiloto, 0, 3, true), fn ($p) => $p['poles'] === 0);
        foreach ($semPole as $p) {
            $nao[] = ['icone' => 'bi-bullseye', 'titulo' => 'Sem pole', 'texto' => $p['nome'] . ' terminou no top 3 do campeonato sem largar nenhuma vez da pole.'];
        }
        if ($titulos['margem'] !== null && $titulos['vice'] && $titulos['margem'] <= 10 && $titulos['margem'] >= 0) {
            $nao[] = ['icone' => 'bi-hourglass-split', 'titulo' => 'Por pouco', 'texto' => $titulos['vice']['nome'] . ' ficou a apenas ' . self::qtd(self::num($titulos['margem']), 'ponto', 'pontos') . ' do título.'];
        }
        if ($titulos['playoff'] && $titulos['playoff']['campeao'] && $campeao && $titulos['playoff']['campeao']['id'] !== $campeao['id']) {
            $nao[] = ['icone' => 'bi-diagram-3', 'titulo' => 'Sem tríplice coroa', 'texto' => 'O campeão do playoff (' . $titulos['playoff']['campeao']['nome'] . ') não foi o campeão geral.'];
        }

        return ['aconteceu' => $sim, 'nao' => $nao, 'dados' => $dados + ['vencedores' => $vencedores, 'maior' => $maior, 'seqVit' => $seqVit, 'dobradinhas' => $dob]];
    }

    private function melhor(array $lista, string $campo)
    {
        $melhor = null;
        foreach ($lista as $p) {
            if ($melhor === null || $p[$campo] > $melhor[$campo]) {
                $melhor = $p;
            }
        }
        return $melhor;
    }

    // =====================================================================================
    // E SE...?
    // =====================================================================================

    private function cenarios(array $porPiloto, array $evolucao): array
    {
        $real = array_key_first($porPiloto);
        $this->posOficial = array_map(fn ($p) => $p['pos'], $porPiloto); // MELHORIA 20261110 (e se: todos os pilotos)
        $cenarios = [];

        foreach (self::SISTEMAS as $chave => $s) {
            $pontos = [];
            $contagem = [];
            foreach ($this->corridas as $idx => $c) {
                foreach ($this->res[$idx] as $r) {
                    $pontos[$r['piloto']] = ($pontos[$r['piloto']] ?? 0) + ($s['pontos'][$r['chegada'] - 1] ?? 0);
                    $contagem[$r['piloto']][$r['chegada']] = ($contagem[$r['piloto']][$r['chegada']] ?? 0) + 1;
                }
            }
            $cenarios[] = $this->cenario($s['nome'], $s['regra'], $pontos, $contagem, $real, $chave === 'vitorias' ? 'vitórias' : 'pts');
        }

        // descartando o pior resultado de cada piloto (regra usada em várias épocas da F1)
        if (count($this->corridas) >= 4) {
            $pontos = [];
            $contagem = [];
            foreach ($porPiloto as $id => $p) {
                $lista = $p['pontosCorrida'];
                $pontos[$id] = array_sum($lista) - ($lista && count($lista) === count($this->corridas) ? min($lista) : 0);
                foreach ($p['chegadas'] as $r) {
                    $contagem[$id][$r['chegada']] = ($contagem[$id][$r['chegada']] ?? 0) + 1;
                }
            }
            $cenarios[] = $this->cenario('Descartando o pior resultado', 'cada piloto que correu todas perde sua pior pontuação', $pontos, $contagem, $real);
        }

        // se as sprints valessem pontos
        $sprints = DB::table('resultados as r')
            ->join('corridas as c', 'c.id', '=', 'r.corrida_id')
            ->join('piloto_equipes as pe', 'pe.id', '=', 'r.pilotoEquipe_id')
            ->where('c.temporada_id', $this->temporada->id)->where('c.user_id', $this->usuario)->where('c.flg_sprint', 'S')
            ->where('r.user_id', $this->usuario)
            ->groupBy('pe.piloto_id')
            ->selectRaw('pe.piloto_id, SUM(r.pontuacao) AS pontos')
            ->pluck('pontos', 'piloto_id');
        if ($sprints->isNotEmpty()) {
            $pontos = [];
            $contagem = [];
            foreach ($porPiloto as $id => $p) {
                $pontos[$id] = $p['pontos'] + (float) ($sprints[$id] ?? 0);
                foreach ($p['chegadas'] as $r) {
                    $contagem[$id][$r['chegada']] = ($contagem[$id][$r['chegada']] ?? 0) + 1;
                }
            }
            $cenarios[] = $this->cenario('Se as sprints valessem pontos', 'pontos das sprints somados aos das corridas', $pontos, $contagem, $real);
        }

        // se a temporada acabasse na metade
        $n = count($this->corridas);
        if ($n >= 4) {
            $meio = $evolucao['metade'];
            $ordem = array_keys($evolucao['posicoesApos'][$meio]);
            $cenarios[] = [
                'nome' => 'Se a temporada acabasse na metade',
                'regra' => 'classificação após a ' . $this->corridas[$meio]['ordem'] . 'ª etapa (' . $this->corridas[$meio]['evento'] . ')',
                'campeao' => $this->p($ordem[0]),
                'mudou' => $ordem[0] !== $real,
                'lista' => $this->listaCenario($ordem, fn ($id) => self::num($evolucao['pontosApos'][$meio][$id]) . ' pts'),
            ];
        }

        return $cenarios;
    }

    private function cenario(string $nome, string $regra, array $pontos, array $contagem, int $real, string $unidade = 'pts'): array
    {
        $ordem = $this->ordenar($pontos, $contagem);
        return [
            'nome' => $nome,
            'regra' => $regra,
            'campeao' => $this->p($ordem[0]),
            'mudou' => $ordem[0] !== $real,
            'lista' => $this->listaCenario($ordem, fn ($id) => $unidade === 'vitórias' ? self::qtd($pontos[$id], 'vitória', 'vitórias') : self::num($pontos[$id]) . ' ' . $unidade),
        ];
    }

    /** posição oficial de cada piloto (para comparar com o cenário) */
    private array $posOficial = [];

    /** classificação completa do cenário + quantas posições cada piloto ganharia/perderia em relação à oficial */
    private function listaCenario(array $ordem, callable $valor): array
    {
        $lista = [];
        foreach ($ordem as $i => $id) {
            $oficial = $this->posOficial[$id] ?? null;
            $lista[] = [
                'pos' => $i + 1,
                'piloto' => $this->p($id),
                'valor' => $valor($id),
                'oficial' => $oficial,
                'delta' => $oficial ? $oficial - ($i + 1) : null, // > 0 = subiria
            ];
        }
        return $lista;
    }

    // =====================================================================================
    // MANCHETES (trechos em forma de notícia)
    // =====================================================================================

    private function manchetes(array $porPiloto, array $porEquipe, array $evolucao, array $duelos, array $titulos, array $dados): array
    {
        $n = count($this->corridas);
        $nomeTemp = $this->temporada->des_temporada . ($this->temporada->referencia ? ' (' . $this->temporada->referencia . ')' : '');
        $finalizada = $this->temporada->flg_finalizada === 'S';
        $campeao = $titulos['piloto'];
        $vice = $titulos['vice'];
        $sorteio = fn (string $chave, array $opcoes) => $opcoes[crc32($this->temporada->id . $chave) % count($opcoes)];
        $m = [];

        // 1. campeão / líder
        if ($campeao) {
            $margem = $titulos['margem'] !== null ? self::num($titulos['margem']) : null;
            if ($finalizada) {
                $titulo = $sorteio('campeao', [
                    $campeao['sobrenome'] . ' é campeão da ' . $this->temporada->des_temporada,
                    'Título confirmado: ' . $campeao['nome'] . ' conquista a ' . $this->temporada->des_temporada,
                    $campeao['sobrenome'] . ' leva a taça para a ' . optional($campeao['equipeInfo'])['nome'],
                ]);
                $texto = $campeao['nome'] . ' (' . optional($campeao['equipeInfo'])['nome'] . ') fechou a ' . $nomeTemp . ' com ' . self::num($campeao['pontos']) . ' pontos, '
                    . self::qtd($campeao['vitorias'], 'vitória', 'vitórias') . ' e ' . self::qtd($campeao['podios'], 'pódio', 'pódios') . '.';
                if ($vice) {
                    $texto .= ' O vice ficou com ' . $vice['nome'] . ', ' . self::qtd($margem, 'ponto', 'pontos') . ' atrás.';
                }
                if ($evolucao['garantido']) {
                    $g = $evolucao['garantido'];
                    $texto .= $g['restantes'] > 0
                        ? ' A conquista foi matematicamente garantida na ' . $this->etapa($g['idx']) . ', com ' . self::qtd($g['restantes'], 'corrida', 'corridas') . ' ainda por disputar.'
                        : ' A decisão ficou para a última etapa.';
                } else {
                    $texto .= ' O título só foi decidido na última corrida.';
                }
            } else {
                $titulo = $sorteio('lider', [
                    $campeao['sobrenome'] . ' lidera após ' . self::qtd($n, 'etapa', 'etapas'),
                    'Na ponta: ' . $campeao['nome'] . ' abre ' . self::qtd($margem ?? 0, 'ponto', 'pontos') . ' de vantagem',
                ]);
                $texto = 'Depois de ' . self::qtd($n, 'corrida', 'corridas') . ', ' . $campeao['nome'] . ' soma ' . self::num($campeao['pontos']) . ' pontos'
                    . ($vice ? ' e tem ' . $vice['nome'] . ' como principal perseguidor, a ' . self::qtd($margem, 'ponto', 'pontos') . '.' : '.');
                $restantes = $this->totalPrevisto - $n;
                if ($restantes > 0) {
                    $texto .= ' Ainda há ' . self::qtd($restantes, 'corrida', 'corridas') . ' pela frente, até ' . self::num($restantes * $evolucao['maxPorCorrida']) . ' pontos em jogo.';
                }
            }
            $m[] = ['tag' => $finalizada ? 'Campeonato' : 'Classificação', 'titulo' => $titulo, 'texto' => $texto, 'foto' => $campeao['img'], 'cor' => optional($campeao['equipeInfo'])['cor']];
        }

        // 2. virada / liderança
        if ($campeao && $evolucao['liderDesde'] !== null && $evolucao['liderDesde'] > 0 && $evolucao['trocas'] > 0) {
            $desde = $evolucao['liderDesde'];
            $antes = $evolucao['lideres'][$desde - 1]['piloto'];
            $m[] = ['tag' => 'Reviravolta', 'titulo' => $sorteio('virada', [
                    'A virada de ' . $campeao['sobrenome'] . ' começou na ' . $this->corridas[$desde]['ordem'] . 'ª etapa',
                    $campeao['sobrenome'] . ' toma a ponta de ' . $antes['sobrenome'] . ' e não larga mais',
                ]),
                'texto' => 'Até a ' . $this->corridas[$desde - 1]['ordem'] . 'ª etapa quem mandava era ' . $antes['nome'] . '. No ' . $this->corridas[$desde]['evento'] . ', '
                    . $campeao['nome'] . ' assumiu a liderança e ' . ($finalizada ? 'a manteve até o fim' : 'segue na frente') . '. Ao todo, a ponta mudou de mãos ' . self::qtd($evolucao['trocas'], 'vez', 'vezes') . '.'];
        } elseif ($evolucao['qtdLideres'] === 1 && $n > 2 && $campeao) {
            $m[] = ['tag' => 'Domínio', 'titulo' => 'De ponta a ponta: ' . $campeao['sobrenome'] . ' liderou todas as etapas',
                'texto' => 'Desde a primeira corrida, ' . $campeao['nome'] . ' não saiu do topo da tabela em nenhum momento da ' . $nomeTemp . '.'];
        }

        // 3. corrida da temporada
        $vl = $dados['vitDeLonge'];
        if ($vl && $vl['r']['largada'] >= 5) {
            $p = $this->p($vl['r']['piloto']);
            $c = $this->corridas[$vl['idx']];
            $m[] = ['tag' => 'Corrida do ano', 'titulo' => $sorteio('corrida', [
                    'De P' . $vl['r']['largada'] . ' ao topo do pódio: ' . $p['sobrenome'] . ' faz a corrida da temporada',
                    $p['sobrenome'] . ' larga em ' . $vl['r']['largada'] . 'º e vence no ' . $c['evento'],
                ]),
                'texto' => 'No ' . $c['evento'] . ($c['pista'] ? ', em ' . $c['pista'] : '') . ', ' . $p['nome'] . ' saiu da ' . $vl['r']['largada'] . 'ª posição e cruzou a linha em primeiro — a vitória vinda de mais longe no grid em toda a temporada.'
                    . ($c['clima'] ? ' Condição de pista: ' . mb_strtolower($c['clima']) . '.' : ''),
                'link' => $c['url'], 'bandeira' => $c['bandeira']];
        } elseif ($dados['escalada'] && $dados['escalada']['ganho'] >= 6) {
            $e = $dados['escalada'];
            $p = $this->p($e['r']['piloto']);
            $c = $this->corridas[$e['idx']];
            $m[] = ['tag' => 'Corrida do ano', 'titulo' => $p['sobrenome'] . ' ganha ' . $e['ganho'] . ' posições no ' . $c['evento'],
                'texto' => 'Largando em ' . $e['r']['largada'] . 'º, ' . $p['nome'] . ' terminou em ' . $e['r']['chegada'] . 'º: a maior recuperação da temporada.', 'link' => $c['url'], 'bandeira' => $c['bandeira']];
        }

        // 4. domínio x equilíbrio
        $maior = $dados['maior'];
        $qtdVenc = count($dados['vencedores']);
        if ($maior && $n >= 4 && $maior['vitorias'] / $n >= 0.5) {
            $m[] = ['tag' => 'Domínio', 'titulo' => $sorteio('dominio', ['Rolo compressor: ' . $maior['sobrenome'] . ' vence ' . $maior['vitorias'] . ' de ' . $n, $maior['sobrenome'] . ' transforma a temporada em monólogo']),
                'texto' => $maior['nome'] . ' venceu ' . self::pct($maior['vitorias'], $n) . ' das corridas' . ($dados['seqVit'] && $dados['seqVit']['seqVitorias'] >= 3 ? ', com direito a ' . $dados['seqVit']['seqVitorias'] . ' vitórias seguidas' : '') . '. Os rivais somaram ' . ($n - $maior['vitorias']) . '.'];
        } elseif ($qtdVenc >= 5 || ($n >= 4 && $qtdVenc >= $n * 0.6)) {
            $m[] = ['tag' => 'Equilíbrio', 'titulo' => $sorteio('equilibrio', ['Temporada aberta: ' . $qtdVenc . ' vencedores diferentes', 'Ninguém manda sozinho: ' . $qtdVenc . ' pilotos venceram']),
                'texto' => 'Em ' . self::qtd($n, 'corrida', 'corridas') . ', ' . $qtdVenc . ' pilotos diferentes subiram ao lugar mais alto do pódio. ' . $maior['nome'] . ' foi quem mais venceu, com ' . self::qtd($maior['vitorias'], 'vitória', 'vitórias') . '.'];
        }

        // 5. construtores
        $eq = $titulos['equipe'];
        if ($eq) {
            $texto = $eq['nome'] . ' ' . ($finalizada ? 'conquistou' : 'lidera') . ' o campeonato de construtores com ' . self::num($eq['pontos']) . ' pontos, '
                . self::qtd($eq['vitorias'], 'vitória', 'vitórias') . ' e ' . self::qtd($eq['podios'], 'pódio', 'pódios');
            $texto .= $eq['dobradinhas'] ? ', incluindo ' . self::qtd($eq['dobradinhas'], 'dobradinha', 'dobradinhas') . '.' : '.';
            if ($titulos['equipeVice']) {
                $texto .= ' ' . $titulos['equipeVice']['nome'] . ' ' . ($finalizada ? 'terminou' : 'está') . ' em segundo, ' . self::qtd(self::num($titulos['margemEquipes']), 'ponto', 'pontos') . ' atrás.';
            }
            $m[] = ['tag' => 'Construtores', 'titulo' => $sorteio('construtores', [
                    $eq['nome'] . ($finalizada ? ' é campeã de construtores' : ' na frente entre as equipes'),
                    ($finalizada ? 'Taça de construtores fica com a ' : 'A ') . $eq['nome'] . ($finalizada ? '' : ' manda no campeonato de equipes'),
                ]), 'texto' => $texto, 'logo' => $eq['img'], 'fundo' => $eq['fundo']];
        }

        // 6. duelo interno mais equilibrado
        $equilibrados = array_filter($duelos, fn ($d) => $d['juntos'] >= 3);
        usort($equilibrados, fn ($a, $b) => [$a['diferenca'], -$a['juntos']] <=> [$b['diferenca'], -$b['juntos']]);
        if ($equilibrados) {
            $d = $equilibrados[0];
            $m[] = ['tag' => 'Duelo interno', 'titulo' => $d['diferenca'] <= 1
                    ? 'Guerra na ' . $d['equipe']['nome'] . ': ' . $d['a']['sobrenome'] . ' x ' . $d['b']['sobrenome'] . ' termina ' . $d['corrida'][0] . ' a ' . $d['corrida'][1]
                    : $d['a']['sobrenome'] . ' leva a melhor sobre ' . $d['b']['sobrenome'] . ' na ' . $d['equipe']['nome'],
                'texto' => 'Nas ' . $d['juntos'] . ' corridas que fizeram juntos, ' . $d['a']['nome'] . ' chegou à frente ' . self::qtd($d['corrida'][0], 'vez', 'vezes') . ' e ' . $d['b']['nome'] . ' ' . self::qtd($d['corrida'][1], 'vez', 'vezes')
                    . '. No grid, o placar foi ' . $d['quali'][0] . ' x ' . $d['quali'][1] . '; em pontos, ' . self::num($d['pontos'][0]) . ' a ' . self::num($d['pontos'][1]) . '.'];
        }

        // 7. torneios paralelos
        $extras = [];
        if ($titulos['playoff'] && $titulos['playoff']['campeao']) {
            $extras[] = $titulos['playoff']['campeao']['nome'] . ' venceu o playoff' . ($titulos['playoff']['vice'] ? ' (final contra ' . $titulos['playoff']['vice']['nome'] . ')' : '');
        }
        if ($titulos['sprint']) {
            $extras[] = $titulos['sprint']['nome'] . ' ' . ($finalizada ? 'ficou com o título' : 'lidera a classificação') . ' das sprints (' . self::qtd($titulos['qtdSprints'], 'sprint', 'sprints') . ')';
        }
        if ($extras) {
            $mesmo = $campeao && $titulos['playoff'] && $titulos['playoff']['campeao'] && $titulos['playoff']['campeao']['id'] === $campeao['id'];
            $m[] = ['tag' => 'Paralelos', 'titulo' => $mesmo ? $campeao['sobrenome'] . ' também leva o playoff' : 'Fora da tabela principal: playoff e sprints',
                'texto' => implode('. ', $extras) . '. Esses torneios não alteram a classificação geral.'];
        }

        // 8. azar
        $maisAb = $this->melhor($porPiloto, 'abandonos');
        if ($maisAb && $maisAb['abandonos'] >= 3) {
            $m[] = ['tag' => 'Azar', 'titulo' => $sorteio('azar', ['Temporada para esquecer: ' . $maisAb['sobrenome'] . ' abandona ' . $maisAb['abandonos'] . ' vezes', $maisAb['sobrenome'] . ' e a coleção de abandonos']),
                'texto' => $maisAb['nome'] . ' não viu a bandeirada em ' . $maisAb['abandonos'] . ' das ' . $maisAb['corridas'] . ' corridas. Mesmo assim terminou em ' . $maisAb['pos'] . 'º no campeonato.'];
        }

        return $m;
    }
}
