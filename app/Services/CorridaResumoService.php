<?php

namespace App\Services;

use App\Http\Controllers\Site\TemporadaController;
use App\Models\Site\Corrida;
use App\Models\Site\Equipe;
use App\Support\ImageUrl;
use Illuminate\Support\Facades\DB;

/**
 * MELHORIA 20261110 (resumo da corrida): manchetes, resultado comentado, impacto no campeonato,
 * marcas históricas (1ª vitória na pista, poles/vitórias seguidas, jejuns), curiosidades e fatos que
 * não aconteceram de UMA corrida.
 *
 * O histórico considera só corridas do mesmo tipo (principal x sprint), em ordem cronológica
 * (temporada, ordem, id — mesmo critério da tela de curiosidades), até a corrida exibida inclusive.
 */
class CorridaResumoService
{
    private int $usuario;
    private Corrida $corrida;
    private bool $sprint;
    private array $seq = [];          // corridas em ordem: [id, temporada, ordem, pista, vr, res[]]
    private int $idx = -1;            // posição da corrida exibida em $seq
    private array $pilotos = [];
    private array $equipes = [];
    private array $pistas = [];

    public function gerar(int $usuario, Corrida $corrida): array
    {
        $this->usuario = $usuario;
        $this->corrida = $corrida->loadMissing(['evento', 'pista.pais', 'temporada']);
        $this->sprint = $corrida->flg_sprint === 'S';
        $this->carregar();

        $c = $this->corrida;
        $temporada = $c->temporada;
        $base = [
            'corrida' => [
                'id' => $c->id,
                'evento' => optional($c->evento)->des_nome ?? optional($c->pista)->nome,
                'pista' => optional($c->pista)->nome,
                'bandeira' => optional(optional($c->pista)->pais)->imagem ? ImageUrl::for($c->pista->pais->imagem) : null,
                'pais' => optional(optional($c->pista)->pais)->des_nome,
                'ordem' => (int) $c->ordem,
                'sprint' => $this->sprint,
                'clima' => $c->condicao_id ? DB::table('condicao_climaticas')->where('id', $c->condicao_id)->value('descricao') : null,
                'safety' => $c->qtd_safety_car,
                'observacoes' => trim((string) $c->observacoes),
                'temporada' => $temporada->des_temporada . ($temporada->referencia ? ' · ' . $temporada->referencia : ''),
                'temporada_id' => $temporada->id,
                'fase' => $this->fasePlayoff(),
            ],
            'vazia' => $this->idx < 0,
            'navegacao' => $this->navegacao(),
        ];
        if ($base['vazia']) {
            return $base;
        }

        $hist = $this->historico();
        $resultado = $this->resultado($hist);
        $campeonato = $this->campeonato($resultado);
        $fatos = $this->fatos($hist, $resultado, $campeonato);

        return $base + [
            'contagem' => $this->contagemEtapa(),
            'kpis' => $this->kpis($resultado, $hist),
            'podio' => array_slice($resultado['linhas'], 0, 3),
            'linhas' => $resultado['linhas'],
            'campeonato' => $campeonato,
            'jejuns' => $fatos['jejuns'],
            'pistaHistorico' => $fatos['pista'],
            'aconteceu' => $fatos['sim'],
            'naoAconteceu' => $fatos['nao'],
            'manchetes' => $this->manchetes($hist, $resultado, $campeonato, $fatos),
        ];
    }

    // =====================================================================================
    // CARGA
    // =====================================================================================

    private function carregar(): void
    {
        $c = $this->corrida;
        $filtroTipo = $this->sprint ? "c.flg_sprint = 'S'" : "(c.flg_sprint IS NULL OR c.flg_sprint <> 'S')";
        $linhas = DB::select("
            SELECT r.corrida_id, r.pilotoEquipe_id AS pe, r.largada, r.chegada, r.pontuacao, r.flg_abandono,
                   pe.piloto_id, pe.equipe_id, c.temporada_id, c.ordem, c.pista_id, c.volta_rapida
              FROM resultados r
              JOIN corridas c ON c.id = r.corrida_id
              JOIN piloto_equipes pe ON pe.id = r.pilotoEquipe_id
             WHERE r.user_id = ? AND c.user_id = ? AND {$filtroTipo}
               AND r.chegada IS NOT NULL AND r.chegada > 0 AND pe.piloto_id <> 0
               AND (c.temporada_id < ? OR (c.temporada_id = ? AND (c.ordem < ? OR (c.ordem = ? AND c.id <= ?))))
             ORDER BY c.temporada_id, c.ordem, c.id, r.chegada, r.id
        ", [$this->usuario, $this->usuario, $c->temporada_id, $c->temporada_id, $c->ordem, $c->ordem, $c->id]);

        $porId = [];
        $idsP = $idsE = $idsPista = [];
        foreach ($linhas as $l) {
            if (!isset($porId[$l->corrida_id])) {
                $porId[$l->corrida_id] = count($this->seq);
                $this->seq[] = ['id' => (int) $l->corrida_id, 'temporada' => (int) $l->temporada_id, 'ordem' => (int) $l->ordem,
                    'pista' => (int) $l->pista_id, 'vr' => $l->volta_rapida ? (int) $l->volta_rapida : null, 'res' => [], 'vistos' => []];
            }
            $k = $porId[$l->corrida_id];
            if (isset($this->seq[$k]['vistos'][$l->piloto_id])) {
                continue; // primeiro registro do piloto na corrida (mesmo critério das outras telas)
            }
            $this->seq[$k]['vistos'][$l->piloto_id] = true;
            $this->seq[$k]['res'][] = [
                'pe' => (int) $l->pe, 'piloto' => (int) $l->piloto_id, 'equipe' => (int) $l->equipe_id,
                'largada' => (int) $l->largada, 'chegada' => (int) $l->chegada, 'pontos' => (float) $l->pontuacao,
                'abandono' => $l->flg_abandono === 'S', 'vr' => $this->seq[$k]['vr'] === (int) $l->pe,
            ];
            $idsP[$l->piloto_id] = $idsE[$l->equipe_id] = $idsPista[$l->pista_id] = true;
        }
        $this->idx = $porId[$c->id] ?? -1;

        $paises = DB::table('paises')->pluck('imagem', 'id');
        foreach (DB::table('pilotos')->whereIn('id', array_keys($idsP))->get(['id', 'nome', 'sobrenome', 'imagem', 'pais_id']) as $p) {
            $this->pilotos[$p->id] = ['id' => (int) $p->id, 'nome' => trim($p->nome . ' ' . $p->sobrenome), 'sobrenome' => $p->sobrenome ?: $p->nome,
                'img' => ImageUrl::for($p->imagem), 'bandeira' => !empty($paises[$p->pais_id]) ? ImageUrl::for($paises[$p->pais_id]) : null,
                'url' => route('pilotos.show', [$p->id])];
        }
        foreach (DB::table('equipes')->whereIn('id', array_keys($idsE))->get(['id', 'nome', 'imagem', 'des_cor']) as $e) {
            $this->equipes[$e->id] = ['id' => (int) $e->id, 'nome' => $e->nome, 'img' => ImageUrl::for($e->imagem), 'cor' => $e->des_cor ?: '#adb5bd',
                'fundo' => Equipe::corFundoIcone($e->des_cor, $e->imagem), 'url' => route('equipes.show', [$e->id])];
        }
        $this->pistas = DB::table('pistas')->whereIn('id', array_keys($idsPista))->pluck('nome', 'id')->all();
    }

    private function p(int $id): array
    {
        return $this->pilotos[$id] ?? ['id' => $id, 'nome' => 'Piloto #' . $id, 'sobrenome' => '#' . $id, 'img' => ImageUrl::PLACEHOLDER, 'bandeira' => null, 'url' => '#'];
    }

    private function e(int $id): array
    {
        return $this->equipes[$id] ?? ['id' => $id, 'nome' => 'Equipe #' . $id, 'img' => ImageUrl::PLACEHOLDER, 'cor' => '#adb5bd', 'fundo' => '#adb5bd', 'url' => '#'];
    }

    private static function qtd($n, string $s, string $p): string
    {
        return $n . ' ' . ($n == 1 ? $s : $p);
    }

    private static function num($v): string
    {
        return floor($v) == $v ? number_format($v, 0, ',', '.') : number_format($v, 1, ',', '.');
    }

    /** "1ª", "2ª"... */
    private static function ord($n): string
    {
        return $n . 'ª';
    }

    private function nomeCorrida(): string
    {
        return $this->sprint ? 'sprint' : 'corrida';
    }

    // =====================================================================================
    // CONTEXTO
    // =====================================================================================

    private function fasePlayoff(): ?array
    {
        $t = $this->corrida->temporada;
        if ($this->sprint || !$t->temPlayoff()) {
            return null;
        }
        $mapa = PlayoffService::mapaFases($t, PlayoffService::corridasPrincipais($this->usuario, $t->id));
        return $mapa[$this->corrida->id] ?? null;
    }

    /** corridas da temporada na mesma ordem da listagem (sprint antes da principal do fim de semana) */
    private function navegacao(): array
    {
        $lista = Corrida::with(['evento', 'pista'])->where('user_id', $this->usuario)->where('temporada_id', $this->corrida->temporada_id)
            ->orderBy('ordem')->orderBy('flg_sprint', 'DESC')->orderBy('id')->get();
        $pos = $lista->search(fn ($x) => $x->id === $this->corrida->id);
        $fmt = fn ($x) => $x ? ['url' => route('corridas.resumo', [$x->id]), 'nome' => ($x->flg_sprint === 'S' ? 'Sprint · ' : $x->ordem . 'ª · ') . (optional($x->evento)->des_nome ?? optional($x->pista)->nome)] : null;
        return [
            'anterior' => $fmt($pos > 0 ? $lista[$pos - 1] : null),
            'proxima' => $fmt($lista[$pos + 1] ?? null),
            'lista' => $lista->map(fn ($x) => ['id' => $x->id, 'url' => route('corridas.resumo', [$x->id]), 'nome' => ($x->flg_sprint === 'S' ? 'Sprint · ' : $x->ordem . 'ª · ') . (optional($x->evento)->des_nome ?? optional($x->pista)->nome)])->values(),
        ];
    }

    private function contagemEtapa(): array
    {
        $t = $this->corrida->temporada;
        $q = Corrida::where('user_id', $this->usuario)->where('temporada_id', $t->id);
        $this->sprint ? $q->where('flg_sprint', 'S') : $q->where(fn ($w) => $w->whereNull('flg_sprint')->orWhere('flg_sprint', '<>', 'S'));
        $total = max($q->count(), $this->sprint ? 0 : (int) $t->qtd_corridas);
        $daTemporada = array_values(array_filter($this->seq, fn ($s) => $s['temporada'] === $t->id));
        $n = count($daTemporada); // corridas com resultado até esta (inclusive)
        return ['n' => $n, 'total' => $total, 'ultima' => $n >= $total];
    }

    // =====================================================================================
    // HISTÓRICO (tudo o que aconteceu ANTES desta corrida)
    // =====================================================================================

    private function historico(): array
    {
        $h = ['pilotos' => [], 'equipes' => [], 'ultimoVencedor' => null, 'seqVit' => 0, 'ultimoPole' => null, 'seqPole' => 0,
            'ultimaEquipe' => null, 'seqEquipe' => 0, 'pistaVencedores' => [], 'pistaPoles' => [], 'edicaoAnterior' => null, 'edicoes' => 0];
        $pistaAtual = $this->seq[$this->idx]['pista'];
        $temporadaAtual = $this->seq[$this->idx]['temporada'];

        for ($k = 0; $k < $this->idx; $k++) {
            $s = $this->seq[$k];
            $vencedor = $pole = null;
            foreach ($s['res'] as $r) {
                $p = &$h['pilotos'][$r['piloto']];
                $p ??= ['corridas' => 0, 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'pontuou' => 0, 'melhor' => null, 'desdeVitoria' => 0,
                    'vitTemporada' => 0, 'corridasTemporada' => 0, 'pistaVit' => 0, 'pistaCorridas' => 0, 'pistaMelhor' => null, 'primeira' => $k];
                $p['corridas']++;
                $p['vitorias'] += $r['chegada'] === 1;
                $p['poles'] += $r['largada'] === 1;
                $p['podios'] += $r['chegada'] <= 3;
                $p['pontuou'] += $r['pontos'] > 0;
                $p['melhor'] = $p['melhor'] === null ? $r['chegada'] : min($p['melhor'], $r['chegada']);
                $p['desdeVitoria'] = $r['chegada'] === 1 ? 0 : $p['desdeVitoria'] + 1;
                if ($s['temporada'] === $temporadaAtual) {
                    $p['corridasTemporada']++;
                    $p['vitTemporada'] += $r['chegada'] === 1;
                }
                if ($s['pista'] === $pistaAtual) {
                    $p['pistaCorridas']++;
                    $p['pistaVit'] += $r['chegada'] === 1;
                    $p['pistaMelhor'] = $p['pistaMelhor'] === null ? $r['chegada'] : min($p['pistaMelhor'], $r['chegada']);
                }
                unset($p);

                $e = &$h['equipes'][$r['equipe']];
                $e ??= ['vitorias' => 0, 'primeira' => $k, 'dobradinhas' => 0];
                $e['vitorias'] += $r['chegada'] === 1;
                unset($e);

                if ($r['chegada'] === 1) {
                    $vencedor = $r;
                }
                if ($r['largada'] === 1) {
                    $pole = $r;
                }
            }
            // sequências globais (corridas consecutivas do mesmo tipo)
            if ($vencedor) {
                $h['seqVit'] = $h['ultimoVencedor'] === $vencedor['piloto'] ? $h['seqVit'] + 1 : 1;
                $h['ultimoVencedor'] = $vencedor['piloto'];
                $h['seqEquipe'] = $h['ultimaEquipe'] === $vencedor['equipe'] ? $h['seqEquipe'] + 1 : 1;
                $h['ultimaEquipe'] = $vencedor['equipe'];
            }
            if ($pole) {
                $h['seqPole'] = $h['ultimoPole'] === $pole['piloto'] ? $h['seqPole'] + 1 : 1;
                $h['ultimoPole'] = $pole['piloto'];
            }
            if ($s['pista'] === $pistaAtual) {
                $h['edicoes']++;
                if ($vencedor) {
                    $h['pistaVencedores'][$vencedor['piloto']] = ($h['pistaVencedores'][$vencedor['piloto']] ?? 0) + 1;
                    $h['edicaoAnterior'] = ['vencedor' => $vencedor['piloto'], 'pole' => $pole['piloto'] ?? null, 'temporada' => $s['temporada'], 'id' => $s['id']];
                }
                if ($pole) {
                    $h['pistaPoles'][$pole['piloto']] = ($h['pistaPoles'][$pole['piloto']] ?? 0) + 1;
                }
            }
        }

        return $h;
    }

    // =====================================================================================
    // RESULTADO DESTA CORRIDA
    // =====================================================================================

    private function resultado(array $h): array
    {
        $s = $this->seq[$this->idx];
        $linhas = [];
        $vencedor = $pole = $vr = null;
        foreach ($s['res'] as $r) {
            $antes = $h['pilotos'][$r['piloto']] ?? null;
            $linha = $r + [
                'p' => $this->p($r['piloto']),
                'eq' => $this->e($r['equipe']),
                'ganho' => $r['largada'] > 0 && !$r['abandono'] ? $r['largada'] - $r['chegada'] : null,
                'estreia' => $antes === null,
                'carreira' => $antes,
            ];
            $linhas[] = $linha;
            if ($r['chegada'] === 1) {
                $vencedor = $linha;
            }
            if ($r['largada'] === 1) {
                $pole = $linha;
            }
            if ($r['vr']) {
                $vr = $linha;
            }
        }
        usort($linhas, fn ($a, $b) => $a['chegada'] <=> $b['chegada']);

        return compact('linhas', 'vencedor', 'pole', 'vr');
    }

    /** classificação antes x depois desta corrida (mesma regra da tela de classificação) */
    private function campeonato(array $resultado): array
    {
        $t = $this->corrida->temporada;
        $tipo = $this->sprint ? 'sprint' : 'principal';
        $ctrl = new TemporadaController();
        $depois = $ctrl->montaClassificacao($this->usuario, $t, (int) $this->corrida->ordem, $tipo)['resultadoPilotos'];
        $antes = $this->corrida->ordem > 1 ? $ctrl->montaClassificacao($this->usuario, $t, (int) $this->corrida->ordem - 1, $tipo)['resultadoPilotos'] : [];

        $posAntes = [];
        $ptsAntes = [];
        foreach ($antes as $i => $l) {
            $posAntes[(int) $l->piloto_id] = $i + 1;
            $ptsAntes[(int) $l->piloto_id] = (float) $l->total;
        }
        $ganhos = [];
        foreach ($resultado['linhas'] as $l) {
            $ganhos[$l['piloto']] = $l['pontos'];
        }

        $tabela = [];
        foreach ($depois as $i => $l) {
            $id = (int) $l->piloto_id;
            $tabela[] = [
                'pos' => $i + 1, 'p' => $this->p($id), 'pontos' => (float) $l->total,
                'ganhou' => $ganhos[$id] ?? 0, 'antes' => $posAntes[$id] ?? null,
                'delta' => isset($posAntes[$id]) ? $posAntes[$id] - ($i + 1) : null,
                'equipe' => $l->equipe_ultima ?? null, 'cor' => $l->equipe_ultima_cor ?? null,
            ];
        }
        $liderAntes = $antes ? (int) $antes[0]->piloto_id : null;
        $liderDepois = $depois ? (int) $depois[0]->piloto_id : null;

        $posDepois = [];
        foreach ($tabela as $l) {
            $posDepois[$l['p']['id']] = $l['pos'];
        }

        return [
            'tabela' => $tabela,
            'liderAntes' => $liderAntes ? $this->p($liderAntes) : null,
            'lider' => $liderDepois ? $this->p($liderDepois) : null,
            'novoLider' => $liderAntes && $liderDepois && $liderAntes !== $liderDepois,
            'vantagem' => count($tabela) > 1 ? $tabela[0]['pontos'] - $tabela[1]['pontos'] : null,
            'vice' => $tabela[1]['p'] ?? null,
            'posAntes' => $posAntes,
            'posDepois' => $posDepois,
            'ptsAntes' => $ptsAntes,
        ];
    }

    private function kpis(array $resultado, array $h): array
    {
        $linhas = $resultado['linhas'];
        $abandonos = count(array_filter($linhas, fn ($l) => $l['abandono']));
        $ganhos = array_sum(array_map(fn ($l) => max(0, (int) $l['ganho']), $linhas));
        $v = $resultado['vencedor'];
        $kpis = [
            ['icone' => 'bi-people-fill', 'rotulo' => 'Largaram', 'valor' => count($linhas), 'detalhe' => self::qtd($abandonos, 'abandono', 'abandonos')],
            ['icone' => 'bi-flag-fill', 'rotulo' => 'Vencedor largou', 'valor' => $v && $v['largada'] > 0 ? 'P' . $v['largada'] : '-', 'detalhe' => $v ? $v['p']['nome'] : ''],
            ['icone' => 'bi-graph-up-arrow', 'rotulo' => 'Posições ganhas', 'valor' => $ganhos, 'detalhe' => 'soma de largada x chegada'],
            ['icone' => 'bi-geo-alt-fill', 'rotulo' => 'Edição na pista', 'valor' => self::ord($h['edicoes'] + 1), 'detalhe' => $h['edicoes'] ? 'já teve ' . self::qtd($h['edicoes'], 'corrida', 'corridas') . ' aqui' : 'estreia da pista'],
        ];
        if ($this->corrida->qtd_safety_car) {
            $kpis[] = ['icone' => 'bi-exclamation-triangle-fill', 'rotulo' => 'Safety cars', 'valor' => (int) $this->corrida->qtd_safety_car, 'detalhe' => 'nesta ' . $this->nomeCorrida()];
        }
        return $kpis;
    }

    // =====================================================================================
    // FATOS
    // =====================================================================================

    private function fatos(array $h, array $res, array $camp): array
    {
        $sim = $nao = [];
        $v = $res['vencedor'];
        $pole = $res['pole'];
        $linhas = $res['linhas'];
        $pistaNome = optional($this->corrida->pista)->nome;
        $d = []; // marcas que viram manchete

        if ($v) {
            $hv = $h['pilotos'][$v['piloto']] ?? null;
            $vitCarreira = ($hv['vitorias'] ?? 0) + 1;
            $vitPista = ($hv['pistaVit'] ?? 0) + 1;
            $seqVit = $h['ultimoVencedor'] === $v['piloto'] ? $h['seqVit'] + 1 : 1;
            $d += compact('vitCarreira', 'vitPista', 'seqVit');
            $d['jejumQuebrado'] = $hv && $hv['vitorias'] > 0 ? $hv['desdeVitoria'] : null;
            $d['primeiraTemporada'] = ($hv['vitTemporada'] ?? 0) === 0;

            if ($vitCarreira === 1) {
                $sim[] = ['icone' => 'bi-star-fill', 'titulo' => '1ª vitória da carreira', 'texto' => $v['p']['nome'] . ' venceu pela primeira vez' . ($hv ? ', na ' . self::ord($hv['corridas'] + 1) . ' ' . $this->nomeCorrida() . ' que disputou.' : ' — logo na estreia!')];
            } else {
                $sim[] = ['icone' => 'bi-trophy-fill', 'titulo' => self::ord($vitCarreira) . ' vitória de ' . $v['p']['sobrenome'], 'texto' => $v['p']['nome'] . ' chega a ' . self::qtd($vitCarreira, 'vitória', 'vitórias') . ' em ' . self::qtd($hv['corridas'] + 1, $this->nomeCorrida(), $this->nomeCorrida() . 's') . '.'];
            }
            if ($vitPista === 1) {
                $sim[] = ['icone' => 'bi-geo-alt-fill', 'titulo' => '1ª vitória em ' . $pistaNome, 'texto' => $v['p']['nome'] . ($hv && $hv['pistaCorridas'] ? ' nunca tinha vencido aqui em ' . self::qtd($hv['pistaCorridas'], 'tentativa', 'tentativas') . ' (melhor resultado: ' . $hv['pistaMelhor'] . 'º).' : ' venceu na primeira vez que correu nesta pista.')];
            } else {
                $recorde = max($h['pistaVencedores'] ?: [0]);
                $sim[] = ['icone' => 'bi-geo-alt-fill', 'titulo' => self::ord($vitPista) . ' vitória em ' . $pistaNome, 'texto' => $v['p']['nome'] . ($vitPista > $recorde ? ' se isola como o maior vencedor da pista.' : ($vitPista === $recorde ? ' iguala o recorde de vitórias na pista.' : ' soma ' . $vitPista . ' vitórias aqui.'))];
            }
            if ($seqVit >= 2) {
                $sim[] = ['icone' => 'bi-fire', 'titulo' => self::ord($seqVit) . ' vitória seguida', 'texto' => $v['p']['nome'] . ' venceu as últimas ' . $seqVit . ' ' . $this->nomeCorrida() . 's.'];
            }
            if ($d['jejumQuebrado'] !== null && $d['jejumQuebrado'] >= 5) {
                $sim[] = ['icone' => 'bi-hourglass-bottom', 'titulo' => 'Fim do jejum', 'texto' => $v['p']['nome'] . ' não vencia havia ' . self::qtd($d['jejumQuebrado'], $this->nomeCorrida(), $this->nomeCorrida() . 's') . '.'];
            }
            if ($d['primeiraTemporada'] && $vitCarreira > 1) {
                $sim[] = ['icone' => 'bi-calendar-check', 'titulo' => '1ª vitória na temporada', 'texto' => $v['p']['nome'] . ' ainda não tinha vencido nesta temporada' . ($hv['corridasTemporada'] ? ' (' . self::qtd($hv['corridasTemporada'], $this->nomeCorrida() . ' anterior', $this->nomeCorrida() . 's anteriores') . ').' : '.')];
            }
            $he = $h['equipes'][$v['equipe']] ?? null;
            if (!$he || $he['vitorias'] === 0) {
                $sim[] = ['icone' => 'bi-shield-fill-check', 'titulo' => '1ª vitória da ' . $v['eq']['nome'], 'texto' => 'A equipe nunca tinha vencido uma ' . $this->nomeCorrida() . ' antes.'];
                $d['primeiraEquipe'] = true;
            }
            if ($v['largada'] > 2) {
                $sim[] = ['icone' => 'bi-lightning-charge-fill', 'titulo' => 'Vitória saindo de P' . $v['largada'], 'texto' => $v['p']['nome'] . ' ganhou ' . ($v['largada'] - 1) . ' ' . ($v['largada'] - 1 === 1 ? 'posição' : 'posições') . ' até a bandeirada.'];
            }
            if ($pole && $v['piloto'] === $pole['piloto'] && $v['vr']) {
                $sim[] = ['icone' => 'bi-stars', 'titulo' => 'Hat-trick', 'texto' => $v['p']['nome'] . ' fez pole, venceu e marcou a volta mais rápida.'];
                $d['hattrick'] = true;
            }
        }

        if ($pole) {
            $hp = $h['pilotos'][$pole['piloto']] ?? null;
            $seqPole = $h['ultimoPole'] === $pole['piloto'] ? $h['seqPole'] + 1 : 1;
            $polesCarreira = ($hp['poles'] ?? 0) + 1;
            $d += compact('seqPole', 'polesCarreira');
            if ($seqPole >= 2) {
                $sim[] = ['icone' => 'bi-bullseye', 'titulo' => self::ord($seqPole) . ' pole seguida', 'texto' => $pole['p']['nome'] . ' largou na frente nas últimas ' . $seqPole . ' ' . $this->nomeCorrida() . 's.'];
            } elseif ($polesCarreira === 1) {
                $sim[] = ['icone' => 'bi-bullseye', 'titulo' => '1ª pole da carreira', 'texto' => $pole['p']['nome'] . ' largou pela primeira vez na pole position.'];
            } else {
                $sim[] = ['icone' => 'bi-bullseye', 'titulo' => self::ord($polesCarreira) . ' pole de ' . $pole['p']['sobrenome'], 'texto' => $pole['p']['nome'] . ' larga na frente' . ($v && $v['piloto'] === $pole['piloto'] ? ' e converte em vitória.' : ', mas terminou em ' . $pole['chegada'] . 'º' . ($pole['abandono'] ? ' (abandono).' : '.'))];
            }
            if ($v && $v['piloto'] !== $pole['piloto']) {
                $nao[] = ['icone' => 'bi-bullseye', 'titulo' => 'A pole não venceu', 'texto' => $pole['p']['nome'] . ' largou na frente e terminou em ' . $pole['chegada'] . 'º' . ($pole['abandono'] ? ', com abandono.' : '.')];
            }
        }

        // dobradinha
        if (isset($linhas[1]) && $linhas[0]['chegada'] === 1 && $linhas[1]['chegada'] === 2 && $linhas[0]['equipe'] === $linhas[1]['equipe']) {
            $sim[] = ['icone' => 'bi-people', 'titulo' => 'Dobradinha da ' . $linhas[0]['eq']['nome'], 'texto' => $linhas[0]['p']['sobrenome'] . ' e ' . $linhas[1]['p']['sobrenome'] . ' fecharam em 1º e 2º.'];
            $d['dobradinha'] = true;
        } else {
            $nao[] = ['icone' => 'bi-people', 'titulo' => 'Sem dobradinha', 'texto' => 'Nenhuma equipe fez 1-2.'];
        }

        // estreias, primeiros pódios/pontos, melhores resultados da carreira
        foreach ($linhas as $l) {
            $c = $l['carreira'];
            if ($l['estreia']) {
                $sim[] = ['icone' => 'bi-person-plus-fill', 'titulo' => 'Estreia', 'texto' => $l['p']['nome'] . ' fez a primeira ' . $this->nomeCorrida() . ' e terminou em ' . $l['chegada'] . 'º' . ($l['abandono'] ? ' (abandono).' : '.')];
            } elseif ($l['chegada'] <= 3 && $c['podios'] === 0) {
                $sim[] = ['icone' => 'bi-award-fill', 'titulo' => '1º pódio de ' . $l['p']['sobrenome'], 'texto' => $l['p']['nome'] . ' subiu ao pódio (' . $l['chegada'] . 'º) pela primeira vez, na ' . self::ord($c['corridas'] + 1) . ' ' . $this->nomeCorrida() . '.'];
            } elseif ($l['pontos'] > 0 && $c['pontuou'] === 0) {
                $sim[] = ['icone' => 'bi-plus-circle-fill', 'titulo' => 'Primeiros pontos', 'texto' => $l['p']['nome'] . ' pontuou pela primeira vez (' . $l['chegada'] . 'º).'];
            } elseif (!$l['abandono'] && $c['melhor'] !== null && $l['chegada'] < $c['melhor'] && $l['chegada'] > 1) {
                $sim[] = ['icone' => 'bi-arrow-up-circle', 'titulo' => 'Melhor resultado da carreira', 'texto' => $l['p']['nome'] . ' terminou em ' . $l['chegada'] . 'º (antes, o melhor era ' . $c['melhor'] . 'º).'];
            }
        }

        // escalada / queda / abandonos
        $comGanho = array_filter($linhas, fn ($l) => $l['ganho'] !== null);
        if ($comGanho) {
            usort($comGanho, fn ($a, $b) => $b['ganho'] <=> $a['ganho']);
            $esc = $comGanho[0];
            $que = end($comGanho);
            if ($esc['ganho'] > 0) {
                $sim[] = ['icone' => 'bi-graph-up-arrow', 'titulo' => 'Escalada: +' . $esc['ganho'] . ' posições', 'texto' => $esc['p']['nome'] . ' largou em ' . $esc['largada'] . 'º e terminou em ' . $esc['chegada'] . 'º.'];
                $d['escalada'] = $esc;
            }
            if ($que['ganho'] < 0) {
                $sim[] = ['icone' => 'bi-graph-down-arrow', 'titulo' => 'Queda: ' . $que['ganho'] . ' posições', 'texto' => $que['p']['nome'] . ' largou em ' . $que['largada'] . 'º e cruzou a linha em ' . $que['chegada'] . 'º.'];
            }
        }
        $abandonos = array_values(array_filter($linhas, fn ($l) => $l['abandono']));
        if ($abandonos) {
            $sim[] = ['icone' => 'bi-cone-striped', 'titulo' => self::qtd(count($abandonos), 'abandono', 'abandonos'), 'texto' => implode(', ', array_map(fn ($l) => $l['p']['sobrenome'], array_slice($abandonos, 0, 8))) . (count($abandonos) > 8 ? ' e outros.' : '.')];
        } else {
            $nao[] = ['icone' => 'bi-cone-striped', 'titulo' => 'Nenhum abandono', 'texto' => 'Todos os ' . count($linhas) . ' pilotos viram a bandeirada.'];
        }
        if ($res['vr']) {
            $sim[] = ['icone' => 'bi-stopwatch', 'titulo' => 'Volta mais rápida', 'texto' => $res['vr']['p']['nome'] . ' (' . $res['vr']['chegada'] . 'º na chegada).'];
        }

        // campeonato
        if ($camp['novoLider']) {
            $sim[] = ['icone' => 'bi-arrow-repeat', 'titulo' => 'Novo líder', 'texto' => $camp['lider']['nome'] . ' assume a ponta do campeonato no lugar de ' . $camp['liderAntes']['nome'] . '.'];
        }
        if ($camp['liderAntes'] && $v && $camp['liderAntes']['id'] !== $v['piloto']) {
            $lider = collect($linhas)->firstWhere('piloto', $camp['liderAntes']['id']);
            $nao[] = ['icone' => 'bi-flag', 'titulo' => 'O líder não venceu', 'texto' => $camp['liderAntes']['nome'] . ' chegou ' . ($lider ? 'em ' . $lider['chegada'] . 'º' . ($lider['abandono'] ? ' (abandono)' : '') : 'sem disputar a ' . $this->nomeCorrida()) . '.'];
        }
        $top3Antes = array_keys(array_filter($camp['posAntes'], fn ($pos) => $pos <= 3));
        $podioIds = array_map(fn ($l) => $l['piloto'], array_slice($linhas, 0, 3));
        if ($top3Antes && !array_intersect($top3Antes, $podioIds)) {
            $nao[] = ['icone' => 'bi-bar-chart-steps', 'titulo' => 'Top 3 fora do pódio', 'texto' => 'Nenhum dos três primeiros do campeonato subiu ao pódio.'];
        }

        // favorito da pista
        if ($h['pistaVencedores']) {
            arsort($h['pistaVencedores']);
            $fav = array_key_first($h['pistaVencedores']);
            $favVit = $h['pistaVencedores'][$fav];
            if ($favVit >= 2 && (!$v || $v['piloto'] !== $fav)) {
                $fl = collect($linhas)->firstWhere('piloto', $fav);
                $nao[] = ['icone' => 'bi-geo', 'titulo' => 'O especialista não venceu', 'texto' => $this->p($fav)['nome'] . ', maior vencedor em ' . $pistaNome . ' (' . $favVit . ' vitórias), ' . ($fl ? 'terminou em ' . $fl['chegada'] . 'º.' : 'não correu desta vez.')];
            }
        }

        // jejuns: quem foi bem e segue sem vencer
        $jejuns = [];
        foreach (array_slice($linhas, 0, 10) as $l) {
            if ($l['chegada'] === 1 || $l['estreia']) {
                continue;
            }
            $c = $l['carreira'];
            $semVencer = $c['desdeVitoria'] + 1; // inclui esta
            $jejuns[] = [
                'p' => $l['p'], 'eq' => $l['eq'], 'chegada' => $l['chegada'],
                'nuncaVenceu' => $c['vitorias'] === 0,
                'corridas' => $c['vitorias'] === 0 ? $c['corridas'] + 1 : $semVencer,
                'vitorias' => $c['vitorias'],
                'semVencerTemporada' => $c['vitTemporada'] === 0,
            ];
        }
        $jejuns = array_values(array_filter($jejuns, fn ($j) => $j['corridas'] >= 2)); // quem venceu a anterior não está em jejum
        usort($jejuns, fn ($a, $b) => $b['corridas'] <=> $a['corridas']);
        foreach (array_slice($jejuns, 0, 3) as $j) {
            if ($j['corridas'] < 3) {
                continue;
            }
            $nao[] = ['icone' => 'bi-hourglass-split', 'titulo' => $j['p']['sobrenome'] . ' segue sem vencer',
                'texto' => $j['nuncaVenceu']
                    ? $j['p']['nome'] . ' (' . $j['chegada'] . 'º hoje) ainda busca a 1ª vitória, após ' . self::qtd($j['corridas'], $this->nomeCorrida(), $this->nomeCorrida() . 's') . '.'
                    : $j['p']['nome'] . ' (' . $j['chegada'] . 'º hoje) não vence há ' . self::qtd($j['corridas'], $this->nomeCorrida(), $this->nomeCorrida() . 's') . '.'];
        }

        // histórico da pista
        $pista = [
            'edicoes' => $h['edicoes'],
            'anterior' => $h['edicaoAnterior'] ? [
                'vencedor' => $this->p($h['edicaoAnterior']['vencedor']),
                'pole' => $h['edicaoAnterior']['pole'] ? $this->p($h['edicaoAnterior']['pole']) : null,
                'temporada' => DB::table('temporadas')->where('id', $h['edicaoAnterior']['temporada'])->value('des_temporada'),
                'url' => route('corridas.resumo', [$h['edicaoAnterior']['id']]),
            ] : null,
            'maiores' => [],
        ];
        $comAtual = $h['pistaVencedores'];
        if ($v) {
            $comAtual[$v['piloto']] = ($comAtual[$v['piloto']] ?? 0) + 1;
        }
        arsort($comAtual);
        foreach (array_slice($comAtual, 0, 5, true) as $id => $q) {
            $pista['maiores'][] = ['p' => $this->p($id), 'vitorias' => $q, 'hoje' => $v && $v['piloto'] === $id];
        }
        if ($pista['anterior'] && $v && $pista['anterior']['vencedor']['id'] === $v['piloto']) {
            $sim[] = ['icone' => 'bi-arrow-clockwise', 'titulo' => 'Repetiu a dose', 'texto' => $v['p']['nome'] . ' já tinha vencido a edição anterior nesta pista.'];
        }

        return ['sim' => $sim, 'nao' => $nao, 'jejuns' => $jejuns, 'pista' => $pista, 'dados' => $d];
    }

    // =====================================================================================
    // MANCHETES
    // =====================================================================================

    private function manchetes(array $h, array $res, array $camp, array $fatos): array
    {
        $v = $res['vencedor'];
        $pole = $res['pole'];
        $d = $fatos['dados'];
        $evento = optional($this->corrida->evento)->des_nome ?? optional($this->corrida->pista)->nome;
        $pistaNome = optional($this->corrida->pista)->nome;
        $sorteio = fn (string $k, array $o) => $o[crc32($this->corrida->id . $k) % count($o)];
        $m = [];
        $linhas = $res['linhas'];

        if ($v) {
            $segundo = $linhas[1] ?? null;
            $terceiro = $linhas[2] ?? null;
            if (($d['vitCarreira'] ?? 0) === 1) {
                $titulo = $sorteio('v', ['Enfim! ' . $v['p']['sobrenome'] . ' conquista a primeira vitória no ' . $evento, 'Dia histórico: ' . $v['p']['nome'] . ' vence pela primeira vez']);
            } elseif (($d['seqVit'] ?? 1) >= 3) {
                $titulo = $sorteio('v', [$v['p']['sobrenome'] . ' não para: ' . $d['seqVit'] . ' vitórias seguidas', 'Imparável, ' . $v['p']['sobrenome'] . ' vence de novo no ' . $evento]);
            } elseif (($d['vitPista'] ?? 0) === 1 && $h['edicoes'] > 0) {
                $titulo = $sorteio('v', [$v['p']['sobrenome'] . ' vence pela primeira vez em ' . $pistaNome, 'Tabu quebrado: ' . $v['p']['sobrenome'] . ' enfim ganha em ' . $pistaNome]);
            } elseif ($v['largada'] >= 5) {
                $titulo = $v['p']['sobrenome'] . ' sai de P' . $v['largada'] . ' e vence o ' . $evento;
            } else {
                $titulo = $sorteio('v', [$v['p']['sobrenome'] . ' vence o ' . $evento, 'Vitória de ' . $v['p']['sobrenome'] . ' no ' . $evento, $v['p']['sobrenome'] . ' leva a melhor em ' . $pistaNome]);
            }
            $texto = $v['p']['nome'] . ' (' . $v['eq']['nome'] . ') ' . ($v['largada'] === 1 ? 'largou na pole e' : ($v['largada'] > 0 ? 'largou em ' . $v['largada'] . 'º e' : '')) . ' venceu ' . ($this->sprint ? 'a sprint' : 'o ' . $evento) . ', em ' . $pistaNome . '.';
            if ($segundo) {
                $texto .= ' ' . $segundo['p']['nome'] . ' foi o segundo' . ($terceiro ? ' e ' . $terceiro['p']['nome'] . ' completou o pódio' : '') . '.';
            }
            $texto .= ' É a ' . self::ord($d['vitCarreira']) . ' vitória da carreira' . (($d['vitPista'] ?? 0) > 1 ? ' e a ' . self::ord($d['vitPista']) . ' nesta pista.' : '.');
            if (!empty($d['jejumQuebrado']) && $d['jejumQuebrado'] >= 5) {
                $texto .= ' O resultado encerra um jejum de ' . self::qtd($d['jejumQuebrado'], $this->nomeCorrida(), $this->nomeCorrida() . 's') . '.';
            }
            $m[] = ['tag' => $this->sprint ? 'Sprint' : 'Vitória', 'titulo' => $titulo, 'texto' => $texto, 'foto' => $v['p']['img'], 'cor' => $v['eq']['cor']];
        }

        // campeonato
        if ($camp['lider']) {
            $vant = $camp['vantagem'] !== null ? self::num($camp['vantagem']) : null;
            $nomeCamp = $this->sprint ? 'a classificação das sprints' : 'o campeonato';
            // vantagem ou empate (desempate pelos resultados)
            $placar = $vant === null ? '' : ($camp['vantagem'] == 0 ? ', empatado em pontos com ' . $camp['vice']['nome'] . ' e na frente pelo desempate' : ', ' . self::qtd($vant, 'ponto', 'pontos') . ' à frente de ' . $camp['vice']['nome']);
            if (!$camp['liderAntes']) {
                $m[] = ['tag' => 'Campeonato', 'titulo' => $camp['lider']['sobrenome'] . ' sai na frente', 'texto' => $camp['lider']['nome'] . ' abre ' . $nomeCamp . ' na liderança' . $placar . '.'];
            } elseif ($camp['novoLider']) {
                $m[] = ['tag' => 'Campeonato', 'titulo' => $camp['lider']['sobrenome'] . ' assume a liderança', 'texto' => 'Com o resultado, ' . $camp['lider']['nome'] . ' tira a ponta de ' . $camp['liderAntes']['nome'] . ' e passa a liderar ' . $nomeCamp . $placar . '.'];
            } elseif ($camp['vantagem'] == 0) {
                $m[] = ['tag' => 'Campeonato', 'titulo' => 'Empate na ponta: ' . $camp['lider']['sobrenome'] . ' segue líder no desempate', 'texto' => $camp['lider']['nome'] . ' continua liderando ' . $nomeCamp . $placar . '.'];
            } else {
                $m[] = ['tag' => 'Campeonato', 'titulo' => $sorteio('c', [$camp['lider']['sobrenome'] . ' segue na ponta com ' . self::qtd($vant, 'ponto', 'pontos') . ' de vantagem', 'Liderança mantida: ' . $camp['lider']['sobrenome'] . ' continua em primeiro']),
                    'texto' => $camp['lider']['nome'] . ' continua liderando ' . $nomeCamp . $placar . '.'];
            }
        }

        // pole
        if ($pole && ($d['seqPole'] ?? 1) >= 2) {
            $m[] = ['tag' => 'Classificação', 'titulo' => $pole['p']['sobrenome'] . ' emenda a ' . self::ord($d['seqPole']) . ' pole seguida', 'texto' => $pole['p']['nome'] . ' largou na frente mais uma vez' . ($v && $v['piloto'] === $pole['piloto'] ? ' e transformou a pole em vitória.' : ', mas terminou em ' . $pole['chegada'] . 'º.')];
        } elseif ($pole && $v && $pole['piloto'] !== $v['piloto']) {
            $m[] = ['tag' => 'Classificação', 'titulo' => 'Pole de ' . $pole['p']['sobrenome'] . ' não vira vitória', 'texto' => $pole['p']['nome'] . ' saiu da pole e cruzou a linha em ' . $pole['chegada'] . 'º' . ($pole['abandono'] ? ', depois de abandonar.' : '.')];
        }

        // escalada
        if (!empty($d['escalada']) && $d['escalada']['ganho'] >= 5 && (!$v || $d['escalada']['piloto'] !== $v['piloto'])) {
            $e = $d['escalada'];
            $m[] = ['tag' => 'Destaque', 'titulo' => $e['p']['sobrenome'] . ' ganha ' . $e['ganho'] . ' posições', 'texto' => 'Largando em ' . $e['largada'] . 'º, ' . $e['p']['nome'] . ' fez a corrida de recuperação do dia e terminou em ' . $e['chegada'] . 'º.'];
        }

        // jejum
        $jejum = collect($fatos['jejuns'])->filter(fn ($j) => $j['chegada'] <= 5 && $j['corridas'] >= 5)->first();
        if ($jejum) {
            $m[] = ['tag' => 'Jejum', 'titulo' => $jejum['nuncaVenceu'] ? $jejum['p']['sobrenome'] . ' bate na trave e segue sem vitórias' : $jejum['p']['sobrenome'] . ' fica no quase de novo',
                'texto' => $jejum['p']['nome'] . ' terminou em ' . $jejum['chegada'] . 'º e ' . ($jejum['nuncaVenceu'] ? 'ainda procura a primeira vitória depois de ' : 'não vence há ') . self::qtd($jejum['corridas'], $this->nomeCorrida(), $this->nomeCorrida() . 's') . '.'];
        }

        // abandonos
        $abandonos = array_values(array_filter($linhas, fn ($l) => $l['abandono']));
        if (count($abandonos) >= 3) {
            $m[] = ['tag' => 'Caos', 'titulo' => self::qtd(count($abandonos), 'abandono', 'abandonos') . ' marcam o ' . $evento, 'texto' => 'Ficaram pelo caminho: ' . implode(', ', array_map(fn ($l) => $l['p']['sobrenome'], array_slice($abandonos, 0, 6))) . (count($abandonos) > 6 ? ' e outros' : '') . '.'];
        }

        return $m;
    }
}
