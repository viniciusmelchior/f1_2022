<?php

namespace App\Services;

use App\Models\Site\Equipe;
use App\Models\Site\Pista;
use App\Support\ImageUrl;
use Illuminate\Support\Facades\DB;

/**
 * MELHORIA 20261117: histórico e estatísticas de uma pista.
 *
 * Junta todas as corridas do usuário disputadas na pista (corridas principais e sprints separadas),
 * os anos de referência das temporadas em que ela apareceu (e os que ela nunca recebeu),
 * rankings de vencedores e curiosidades. Só leitura: não altera largada/chegada/pontuação.
 */
class PistaHistoricoService
{
    private int $usuario;
    private array $pilotos = [];
    private array $equipes = [];

    public function gerar(int $usuario, Pista $pista): array
    {
        $this->usuario = $usuario;
        $pista->loadMissing(['pais.continente', 'autor']);

        $corridas = $this->carregarCorridas($pista->id);
        $principais = array_values(array_filter($corridas, fn ($c) => !$c['sprint']));
        $sprints = array_values(array_filter($corridas, fn ($c) => $c['sprint']));

        // temporadas do usuário (ordem cronológica) para medir presença no calendário e ausências
        $temporadas = DB::table('temporadas')->where('user_id', $usuario)->orderBy('id')->get(['id', 'des_temporada', 'referencia']);
        $referencias = $this->referencias($corridas, $temporadas);

        return [
            'pista' => [
                'id' => $pista->id,
                'nome' => $pista->nome,
                'pais' => optional($pista->pais)->des_nome,
                'bandeira' => optional($pista->pais)->imagem ? ImageUrl::for($pista->pais->imagem) : null,
                'continente' => optional(optional($pista->pais)->continente)->nome,
                'autor' => optional($pista->autor)->nome,
                'drs' => $pista->drs,
                'tipo' => $pista->tipo,
                'qtd_carros' => $pista->qtd_carros,
                'tamanho_km' => $pista->tamanho_km,
                'qtd_voltas' => $pista->qtd_voltas,
                'ativa' => $pista->flg_ativo === 'S',
            ],
            'vazia' => empty($corridas),
            'kpis' => $this->kpis($principais, $sprints, $temporadas, $referencias),
            'referencias' => $referencias,
            'corridas' => $principais,
            'sprints' => $sprints,
            'rankingPilotos' => $this->rankingPilotos($principais),
            'rankingEquipes' => $this->rankingEquipes($principais),
            'rankingPilotosSprint' => $this->rankingPilotos($sprints),
            'curiosidades' => $this->curiosidades($principais, $temporadas, 'corrida', 'corridas'),
            'curiosidadesSprint' => $this->curiosidades($sprints, $temporadas, 'sprint', 'sprints'),
        ];
    }

    // =====================================================================================
    // CARGA
    // =====================================================================================

    private function carregarCorridas(int $pistaId): array
    {
        $linhas = DB::table('corridas as c')
            ->leftJoin('temporadas as t', 't.id', '=', 'c.temporada_id')
            ->leftJoin('anos as a', 'a.id', '=', 't.ano_id')
            ->leftJoin('eventos as e', 'e.id', '=', 'c.evento_id')
            ->leftJoin('condicao_climaticas as cc', 'cc.id', '=', 'c.condicao_id')
            ->where('c.user_id', $this->usuario)
            ->where('c.pista_id', $pistaId)
            ->orderBy('c.temporada_id')->orderBy('c.ordem')->orderBy('c.id')
            ->get(['c.id', 'c.temporada_id', 'c.ordem', 'c.flg_sprint', 'c.volta_rapida', 'c.qtd_safety_car', 'c.dificuldade_ia',
                't.des_temporada', 't.referencia', 'a.ano', 'e.des_nome as evento', 'cc.descricao as clima', 'cc.des_icone as clima_icone']);

        if ($linhas->isEmpty()) {
            return [];
        }

        // resultados: primeiro registro de cada piloto na corrida (mesmo critério dos resumos)
        $res = [];
        $vistos = [];
        $idsP = $idsE = [];
        $rows = DB::table('resultados as r')
            ->join('piloto_equipes as pe', 'pe.id', '=', 'r.pilotoEquipe_id')
            ->where('r.user_id', $this->usuario)
            ->whereIn('r.corrida_id', $linhas->pluck('id'))
            ->where('pe.piloto_id', '<>', 0)
            ->orderBy('r.corrida_id')->orderBy('r.chegada')->orderBy('r.id')
            ->get(['r.corrida_id', 'r.pilotoEquipe_id as pe', 'r.largada', 'r.chegada', 'r.flg_abandono', 'pe.piloto_id', 'pe.equipe_id']);
        foreach ($rows as $r) {
            if (isset($vistos[$r->corrida_id][$r->piloto_id])) {
                continue;
            }
            $vistos[$r->corrida_id][$r->piloto_id] = true;
            $res[$r->corrida_id][] = ['pe' => (int) $r->pe, 'piloto' => (int) $r->piloto_id, 'equipe' => (int) $r->equipe_id,
                'largada' => (int) $r->largada, 'chegada' => (int) $r->chegada, 'abandono' => $r->flg_abandono === 'S'];
            $idsP[$r->piloto_id] = $idsE[$r->equipe_id] = true;
        }
        $this->carregarNomes(array_keys($idsP), array_keys($idsE));

        $corridas = [];
        foreach ($linhas as $l) {
            $lista = $res[$l->id] ?? [];
            $porChegada = fn ($pos) => collect($lista)->first(fn ($x) => $x['chegada'] === $pos);
            $pole = collect($lista)->first(fn ($x) => $x['largada'] === 1);
            $vr = $l->volta_rapida ? collect($lista)->first(fn ($x) => $x['pe'] === (int) $l->volta_rapida) : null;
            $temporada = $l->des_temporada ?? ('Temporada #' . $l->temporada_id);

            $corridas[] = [
                'id' => (int) $l->id,
                'sprint' => $l->flg_sprint === 'S',
                'temporada_id' => (int) $l->temporada_id,
                'temporada' => $temporada,
                'ano' => $l->ano,
                'referencia' => filled($l->referencia) ? trim($l->referencia) : null,
                'ordem' => (int) $l->ordem,
                'evento' => $l->evento,
                'clima' => $l->clima,
                'clima_icone' => $l->clima_icone,
                'safety_car' => (int) $l->qtd_safety_car,
                'ia' => $l->dificuldade_ia,
                'tem_resultado' => !empty($lista),
                'participantes' => count($lista),
                'abandonos' => collect($lista)->where('abandono', true)->count(),
                'pole' => $pole ? $this->x($pole) : null,
                'p1' => ($v = $porChegada(1)) ? $this->x($v) + ['largada' => $v['largada']] : null,
                'p2' => ($v = $porChegada(2)) ? $this->x($v) : null,
                'p3' => ($v = $porChegada(3)) ? $this->x($v) : null,
                'vr' => $vr ? $this->x($vr) : null,
                'res' => $lista,
            ];
        }

        return $corridas;
    }

    private function carregarNomes(array $idsP, array $idsE): void
    {
        foreach (DB::table('pilotos')->whereIn('id', $idsP)->get(['id', 'nome', 'sobrenome', 'imagem']) as $p) {
            $this->pilotos[$p->id] = ['id' => (int) $p->id, 'nome' => trim($p->nome . ' ' . $p->sobrenome),
                'img' => ImageUrl::for($p->imagem), 'url' => route('pilotos.show', [$p->id])];
        }
        foreach (DB::table('equipes')->whereIn('id', $idsE)->get(['id', 'nome', 'imagem', 'des_cor']) as $e) {
            $this->equipes[$e->id] = ['id' => (int) $e->id, 'nome' => $e->nome, 'img' => ImageUrl::for($e->imagem),
                'cor' => $e->des_cor ?: '#adb5bd', 'fundo' => Equipe::corFundoIcone($e->des_cor, $e->imagem), 'url' => route('equipes.show', [$e->id])];
        }
    }

    private function p(int $id): array
    {
        return $this->pilotos[$id] ?? ['id' => $id, 'nome' => 'Piloto #' . $id, 'img' => ImageUrl::PLACEHOLDER, 'url' => '#'];
    }

    private function e(int $id): array
    {
        return $this->equipes[$id] ?? ['id' => $id, 'nome' => 'Equipe #' . $id, 'img' => ImageUrl::PLACEHOLDER, 'cor' => '#adb5bd', 'fundo' => '#adb5bd', 'url' => '#'];
    }

    /** piloto + equipe de uma linha de resultado */
    private function x(array $r): array
    {
        return ['p' => $this->p($r['piloto']), 'eq' => $this->e($r['equipe'])];
    }

    private static function qtd($n, string $s, string $p): string
    {
        return $n . ' ' . ($n == 1 ? $s : $p);
    }

    private static function rotulo(array $c): string
    {
        return $c['temporada'] . ($c['referencia'] ? ' (ref. ' . $c['referencia'] . ')' : '');
    }

    // =====================================================================================
    // REFERÊNCIAS
    // =====================================================================================

    /** anos de referência que a pista já recebeu x anos de referência das temporadas do usuário que ela nunca recebeu */
    private function referencias(array $corridas, $temporadas): array
    {
        $recebeu = [];
        $semReferencia = ['corridas' => 0, 'sprints' => 0, 'temporadas' => []];
        foreach ($corridas as $c) {
            if (!$c['referencia']) {
                $semReferencia[$c['sprint'] ? 'sprints' : 'corridas']++;
                $semReferencia['temporadas'][$c['temporada_id']] = $c['temporada'];
                continue;
            }
            $r = &$recebeu[$c['referencia']];
            $r ??= ['ano' => $c['referencia'], 'corridas' => 0, 'sprints' => 0, 'temporadas' => [], 'vencedores' => []];
            $r[$c['sprint'] ? 'sprints' : 'corridas']++;
            $r['temporadas'][$c['temporada_id']] = $c['temporada'];
            if (!$c['sprint'] && $c['p1']) {
                $r['vencedores'][] = $c['p1']['p']['nome'] . ' · ' . $c['temporada'];
            }
            unset($r);
        }
        uksort($recebeu, fn ($a, $b) => strnatcmp((string) $a, (string) $b));

        // temporadas do usuário por referência, para dizer quantas oportunidades a pista "perdeu"
        $temporadasPorRef = [];
        foreach ($temporadas as $t) {
            if (filled($t->referencia)) {
                $temporadasPorRef[trim($t->referencia)][] = $t->des_temporada;
            }
        }
        $nunca = [];
        foreach ($temporadasPorRef as $ano => $lista) {
            if (!isset($recebeu[$ano])) {
                $nunca[$ano] = ['ano' => (string) $ano, 'temporadas' => $lista];
            }
        }
        uksort($nunca, fn ($a, $b) => strnatcmp((string) $a, (string) $b));

        foreach ($recebeu as $ano => &$r) {
            $r['ano'] = (string) $ano;
            $r['temporadas'] = array_values($r['temporadas']);
            $r['total_temporadas_ref'] = count($temporadasPorRef[$ano] ?? []);
        }
        unset($r);

        return [
            'recebeu' => array_values($recebeu),
            'nunca' => array_values($nunca),
            'sem' => $semReferencia['corridas'] + $semReferencia['sprints'] > 0
                ? $semReferencia + ['temporadas' => array_values($semReferencia['temporadas'])] : null,
            'todas' => array_values(array_unique(array_merge(array_keys($recebeu), array_keys($temporadasPorRef)))),
        ];
    }

    // =====================================================================================
    // KPIs
    // =====================================================================================

    private function kpis(array $principais, array $sprints, $temporadas, array $refs): array
    {
        $todas = array_merge($principais, $sprints);
        $temporadasPista = collect($todas)->pluck('temporada_id')->unique()->count();
        $ultima = collect($todas)->sortBy([['temporada_id', 'desc'], ['ordem', 'desc']])->first();
        $primeira = collect($todas)->sortBy([['temporada_id', 'asc'], ['ordem', 'asc']])->first();
        $totalRefs = count($refs['recebeu']) + count($refs['nunca']);

        return [
            ['icone' => 'bi-flag-fill', 'rotulo' => 'Corridas', 'valor' => count($principais), 'detalhe' => count($principais) ? self::qtd(collect($principais)->filter(fn ($c) => $c['p1'])->pluck('p1.p.id')->unique()->count(), 'vencedor diferente', 'vencedores diferentes') : 'nenhuma corrida principal'],
            ['icone' => 'bi-lightning-charge-fill', 'rotulo' => 'Sprints', 'valor' => count($sprints), 'detalhe' => count($sprints) ? 'contadas à parte' : 'nunca recebeu sprint'],
            ['icone' => 'bi-calendar3', 'rotulo' => 'Temporadas', 'valor' => $temporadasPista, 'detalhe' => 'de ' . $temporadas->count() . ' (' . ($temporadas->count() ? round($temporadasPista * 100 / $temporadas->count()) : 0) . '% do total)'],
            ['icone' => 'bi-bookmark-star-fill', 'rotulo' => 'Anos de referência', 'valor' => count($refs['recebeu']), 'detalhe' => $totalRefs ? 'de ' . $totalRefs . ' já usados nas temporadas' : 'sem referências cadastradas'],
            ['icone' => 'bi-clock-history', 'rotulo' => 'Última aparição', 'valor' => $ultima ? ($ultima['referencia'] ?? '-') : '-', 'detalhe' => $ultima ? $ultima['temporada'] . ($primeira && $primeira['id'] !== $ultima['id'] ? ' · estreia: ' . $primeira['temporada'] : ' · estreia') : 'nunca utilizada'],
        ];
    }

    // =====================================================================================
    // RANKINGS
    // =====================================================================================

    private function rankingPilotos(array $corridas): array
    {
        $r = [];
        foreach ($corridas as $c) {
            foreach ($c['res'] as $x) {
                $id = $x['piloto'];
                $r[$id] ??= ['p' => $this->p($id), 'eq' => null, 'disputas' => 0, 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'vr' => 0, 'abandonos' => 0, 'melhor' => null, 'soma' => 0, 'terminou' => 0];
                $r[$id]['eq'] = $this->e($x['equipe']); // última equipe com que correu aqui
                $r[$id]['disputas']++;
                $r[$id]['vitorias'] += $x['chegada'] === 1;
                $r[$id]['poles'] += $x['largada'] === 1;
                $r[$id]['podios'] += $x['chegada'] >= 1 && $x['chegada'] <= 3;
                $r[$id]['vr'] += $c['vr'] && $c['vr']['p']['id'] === $id;
                $r[$id]['abandonos'] += $x['abandono'];
                if ($x['chegada'] > 0) {
                    $r[$id]['melhor'] = $r[$id]['melhor'] === null ? $x['chegada'] : min($r[$id]['melhor'], $x['chegada']);
                    if (!$x['abandono']) {
                        $r[$id]['soma'] += $x['chegada'];
                        $r[$id]['terminou']++;
                    }
                }
            }
        }
        foreach ($r as &$l) {
            $l['media'] = $l['terminou'] ? round($l['soma'] / $l['terminou'], 1) : null;
        }
        unset($l);
        usort($r, fn ($a, $b) => [$b['vitorias'], $b['podios'], $b['poles'], $a['melhor'] ?? 99] <=> [$a['vitorias'], $a['podios'], $a['poles'], $b['melhor'] ?? 99]);

        return $r;
    }

    private function rankingEquipes(array $corridas): array
    {
        $r = [];
        foreach ($corridas as $c) {
            foreach (['p1' => 'vitorias', 'pole' => 'poles'] as $campo => $chave) {
                if ($c[$campo]) {
                    $id = $c[$campo]['eq']['id'];
                    $r[$id] ??= ['eq' => $c[$campo]['eq'], 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'dobradinhas' => 0];
                    $r[$id][$chave]++;
                }
            }
            foreach (['p1', 'p2', 'p3'] as $campo) {
                if ($c[$campo]) {
                    $id = $c[$campo]['eq']['id'];
                    $r[$id] ??= ['eq' => $c[$campo]['eq'], 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'dobradinhas' => 0];
                    $r[$id]['podios']++;
                }
            }
            if ($c['p1'] && $c['p2'] && $c['p1']['eq']['id'] === $c['p2']['eq']['id']) {
                $r[$c['p1']['eq']['id']]['dobradinhas']++;
            }
        }
        usort($r, fn ($a, $b) => [$b['vitorias'], $b['podios'], $b['poles']] <=> [$a['vitorias'], $a['podios'], $a['poles']]);

        return $r;
    }

    // =====================================================================================
    // CURIOSIDADES
    // =====================================================================================

    private function curiosidades(array $corridas, $temporadas, string $s, string $pl): array
    {
        $comResultado = array_values(array_filter($corridas, fn ($c) => $c['p1']));
        if (empty($corridas)) {
            return [];
        }
        $f = [];
        $total = count($corridas);

        // estreia
        $primeira = $corridas[0];
        $f[] = ['icone' => 'bi-door-open-fill', 'titulo' => 'Estreia: ' . self::rotulo($primeira),
            'texto' => 'A 1ª ' . $s . ' aqui foi a ' . ($primeira['sprint'] ? 'sprint' : $primeira['ordem'] . 'ª etapa') . ' da ' . $primeira['temporada']
                . ($primeira['p1'] ? ', vencida por ' . $primeira['p1']['p']['nome'] . ' (' . $primeira['p1']['eq']['nome'] . ').' : '.')];

        // referência mais frequente
        $porRef = collect($corridas)->filter(fn ($c) => $c['referencia'])->countBy('referencia')->sortDesc();
        if ($porRef->isNotEmpty()) {
            $top = $porRef->keys()->first();
            $f[] = ['icone' => 'bi-bookmark-star-fill', 'titulo' => 'Ano de referência mais comum: ' . $top,
                'texto' => self::qtd($porRef->first(), $s, $pl) . ' com referência ' . $top . '. Ao todo, ' . ($porRef->count() === 1 ? 'só esse ano de referência passou por aqui.' : $porRef->count() . ' anos de referência diferentes já passaram por aqui.')];
        }

        // presença no calendário / maior ausência (contada em temporadas do usuário)
        $ordemTemp = $temporadas->pluck('id')->flip();
        $tempsPista = collect($corridas)->pluck('temporada_id')->unique()->sort()->values();
        $maiorGap = 0;
        $gapDe = $gapAte = null;
        for ($i = 1; $i < $tempsPista->count(); $i++) {
            $gap = ($ordemTemp[$tempsPista[$i]] ?? 0) - ($ordemTemp[$tempsPista[$i - 1]] ?? 0) - 1;
            if ($gap > $maiorGap) {
                [$maiorGap, $gapDe, $gapAte] = [$gap, $tempsPista[$i - 1], $tempsPista[$i]];
            }
        }
        if ($maiorGap > 0) {
            $nome = fn ($id) => optional($temporadas->firstWhere('id', $id))->des_temporada;
            $f[] = ['icone' => 'bi-hourglass-split', 'titulo' => 'Maior ausência: ' . self::qtd($maiorGap, 'temporada', 'temporadas'),
                'texto' => 'Ficou fora do calendário de ' . $s . 's entre a ' . $nome($gapDe) . ' e a ' . $nome($gapAte) . '.'];
        }
        $repetidas = collect($corridas)->countBy('temporada_id')->filter(fn ($q) => $q > 1);
        if ($repetidas->isNotEmpty()) {
            $f[] = ['icone' => 'bi-arrow-repeat', 'titulo' => 'Mais de uma vez na mesma temporada',
                'texto' => 'Recebeu ' . $pl . ' repetidas em ' . self::qtd($repetidas->count(), 'temporada', 'temporadas') . ' (máximo de ' . $repetidas->max() . ' numa só).'];
        }

        if ($comResultado) {
            // maior vencedor / rei da pole
            $vit = collect($comResultado)->countBy(fn ($c) => $c['p1']['p']['id'])->sortDesc();
            $idV = $vit->keys()->first();
            $empatados = $vit->filter(fn ($q) => $q === $vit->first())->count();
            $f[] = ['icone' => 'bi-trophy-fill', 'titulo' => 'Maior vencedor: ' . $this->p($idV)['nome'],
                'texto' => self::qtd($vit->first(), 'vitória', 'vitórias') . ' em ' . self::qtd(count($comResultado), $s, $pl) . ' com resultado' . ($empatados > 1 ? ', empatado com mais ' . ($empatados - 1) . '.' : '.')];

            $poles = collect($comResultado)->filter(fn ($c) => $c['pole'])->countBy(fn ($c) => $c['pole']['p']['id'])->sortDesc();
            if ($poles->isNotEmpty()) {
                $f[] = ['icone' => 'bi-stopwatch-fill', 'titulo' => 'Rei da pole: ' . $this->p($poles->keys()->first())['nome'],
                    'texto' => self::qtd($poles->first(), 'pole position', 'pole positions') . ' por aqui.'];
            }

            $diferentes = $vit->count();
            if (count($comResultado) > 1) {
                $f[] = ['icone' => 'bi-people-fill', 'titulo' => self::qtd($diferentes, 'vencedor diferente', 'vencedores diferentes'),
                    'texto' => $diferentes === count($comResultado) ? 'Ninguém venceu aqui duas vezes.' : 'Em ' . self::qtd(count($comResultado), $s, $pl) . ' com resultado.'];
            }

            // pole convertida
            $daPole = collect($comResultado)->filter(fn ($c) => $c['p1']['largada'] === 1)->count();
            $f[] = ['icone' => 'bi-1-circle-fill', 'titulo' => round($daPole * 100 / count($comResultado)) . '% vencidas da pole',
                'texto' => self::qtd($daPole, $s . ' foi vencida', $pl . ' foram vencidas') . ' por quem largou em 1º.'];

            // vitória de mais longe
            $longe = collect($comResultado)->sortByDesc(fn ($c) => $c['p1']['largada'])->first();
            if ($longe['p1']['largada'] > 1) {
                $f[] = ['icone' => 'bi-graph-up-arrow', 'titulo' => 'Vitória de mais longe: ' . $longe['p1']['largada'] . 'º no grid',
                    'texto' => $longe['p1']['p']['nome'] . ' venceu largando em ' . $longe['p1']['largada'] . 'º na ' . self::rotulo($longe) . '.'];
            }

            // maior sequência de vitórias seguidas na pista
            $seq = $melhor = 0;
            $anterior = $quem = null;
            foreach ($comResultado as $c) {
                $seq = $c['p1']['p']['id'] === $anterior ? $seq + 1 : 1;
                $anterior = $c['p1']['p']['id'];
                if ($seq > $melhor) {
                    [$melhor, $quem] = [$seq, $anterior];
                }
            }
            if ($melhor > 1) {
                $f[] = ['icone' => 'bi-fire', 'titulo' => self::qtd($melhor, 'vitória seguida', 'vitórias seguidas'),
                    'texto' => $this->p($quem)['nome'] . ' tem a maior sequência de vitórias consecutivas na pista.'];
            }

            // hat-trick e dobradinhas
            $hat = collect($comResultado)->filter(fn ($c) => $c['pole'] && $c['vr'] && $c['pole']['p']['id'] === $c['p1']['p']['id'] && $c['vr']['p']['id'] === $c['p1']['p']['id']);
            if ($hat->isNotEmpty()) {
                $f[] = ['icone' => 'bi-stars', 'titulo' => self::qtd($hat->count(), 'hat-trick', 'hat-tricks'),
                    'texto' => 'Pole, vitória e volta mais rápida no mesmo dia. Último: ' . $hat->last()['p1']['p']['nome'] . ', na ' . self::rotulo($hat->last()) . '.'];
            }
            $dob = collect($comResultado)->filter(fn ($c) => $c['p2'] && $c['p1']['eq']['id'] === $c['p2']['eq']['id']);
            if ($dob->isNotEmpty()) {
                $f[] = ['icone' => 'bi-people', 'titulo' => self::qtd($dob->count(), 'dobradinha', 'dobradinhas'),
                    'texto' => 'Última: ' . $dob->last()['p1']['eq']['nome'] . ' na ' . self::rotulo($dob->last()) . '.'];
            }

            // abandonos
            $caos = collect($comResultado)->sortByDesc('abandonos')->first();
            if ($caos['abandonos'] > 0) {
                $media = round(collect($comResultado)->avg('abandonos'), 1);
                $f[] = ['icone' => 'bi-exclamation-octagon-fill', 'titulo' => 'Mais caótica: ' . self::qtd($caos['abandonos'], 'abandono', 'abandonos'),
                    'texto' => 'Na ' . self::rotulo($caos) . '. Média de ' . str_replace('.', ',', $media) . ' por ' . $s . '.'];
            }
        }

        // safety car e clima
        $sc = collect($corridas)->where('safety_car', '>', 0);
        if ($sc->isNotEmpty()) {
            $f[] = ['icone' => 'bi-car-front-fill', 'titulo' => 'Safety car em ' . self::qtd($sc->count(), $s, $pl),
                'texto' => self::qtd($sc->sum('safety_car'), 'entrada', 'entradas') . ' do safety car no total (' . round($sc->count() * 100 / $total) . '% das ' . $pl . ').'];
        }
        $climas = collect($corridas)->filter(fn ($c) => $c['clima'])->countBy('clima');
        if ($climas->count() > 1) {
            $f[] = ['icone' => 'bi-cloud-sun-fill', 'titulo' => 'Clima variado',
                'texto' => $climas->map(fn ($q, $nome) => $nome . ': ' . $q)->implode(' · ')];
        }

        return $f;
    }
}
