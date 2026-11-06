<?php

namespace App\Services;

use App\Models\Site\Equipe;
use App\Support\ImageUrl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 20261105 - Tela de curiosidades e recordes.
 *
 * Considera apenas Grandes Prêmios (corridas.flg_sprint = 'N'). Todos os resultados são lidos em
 * uma única consulta e processados em memória, em ordem cronológica (temporada, ordem da corrida).
 * O resultado fica em cache e é recalculado automaticamente quando os dados mudam (assinatura()).
 */
class CuriosidadesService
{
    /** Quantos itens cada ranking guarda (a tela mostra 8 e expande até este limite) */
    private const LIMITE = 20;

    private const CONTINENTES = [
        'Europa' => 'Europa', 'Asia' => 'Ásia', 'America do Sul' => 'América do Sul',
        'America do Norte' => 'América do Norte', 'Oceania' => 'Oceania', 'Africa' => 'África',
        'Ficticio' => 'Fictício',
    ];

    private $pilotos;
    private $equipes;
    private $pistas;
    private $paises;
    private $eventos;
    private $temporadas;
    private $continentes;
    private array $corridas = [];
    private array $cacheEquipe = [];
    private array $cachePiloto = [];

    /** Dados da tela. $assinatura evita recalcular a assinatura quando o chamador já a tem */
    public function gerar(int $userId, bool $forcar = false, ?string $assinatura = null): array
    {
        $chave = 'curiosidades_v3_'.$userId.'_'.($assinatura ?? $this->assinatura($userId));
        if ($forcar) {
            Cache::forget($chave);
        }

        return Cache::remember($chave, now()->addDays(15), fn () => $this->calcular($userId));
    }

    public function esquecer(int $userId, string $assinatura): void
    {
        Cache::forget('curiosidades_v3_'.$userId.'_'.$assinatura);
    }

    /** Checksum barato (~30 ms) de tudo que influencia a tela: qualquer alteração gera uma chave de cache nova */
    public function assinatura(int $userId): string
    {
        $linha = DB::selectOne("
            SELECT
                (SELECT CONCAT(COUNT(*), '-', COALESCE(SUM(CRC32(CONCAT_WS('|', id, pilotoEquipe_id, largada, chegada, flg_abandono))), 0))
                   FROM resultados WHERE user_id = ?) AS resultados,
                (SELECT CONCAT(COUNT(*), '-', COALESCE(SUM(CRC32(CONCAT_WS('|', id, temporada_id, pista_id, evento_id, ordem, volta_rapida, flg_sprint))), 0))
                   FROM corridas WHERE user_id = ?) AS corridas,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, piloto_id, equipe_id))), 0) FROM piloto_equipes) AS duplas,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, nome, sobrenome, imagem, pais_id, flg_ativo))), 0) FROM pilotos) AS pilotos,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, nome, imagem, des_cor, pais_id, flg_ativo))), 0) FROM equipes) AS equipes,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, nome, pais_id))), 0) FROM pistas) AS pistas,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, des_nome, imagem, continente_id))), 0) FROM paises) AS paises,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, des_nome))), 0) FROM eventos) AS eventos,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, des_temporada, referencia))), 0) FROM temporadas WHERE user_id = ?) AS temporadas,
                (SELECT COALESCE(SUM(CRC32(CONCAT_WS('|', id, temporada_id, pilotoEquipe_id))), 0) FROM titulos WHERE user_id = ?) AS titulos
        ", [$userId, $userId, $userId, $userId]);

        return md5(json_encode($linha));
    }

    // =====================================================================================
    // CARGA
    // =====================================================================================

    private function carregar(int $userId): void
    {
        $linhas = DB::select("
            SELECT r.corrida_id, r.pilotoEquipe_id AS pe, r.largada, r.chegada, r.flg_abandono,
                   pe.piloto_id, pe.equipe_id,
                   c.temporada_id, c.ordem, c.pista_id, c.evento_id, c.volta_rapida
              FROM resultados r
              JOIN corridas c ON c.id = r.corrida_id
              JOIN piloto_equipes pe ON pe.id = r.pilotoEquipe_id
             WHERE r.user_id = ?
               AND c.user_id = ?
               AND c.flg_sprint = 'N'
               AND pe.piloto_id <> 0
             ORDER BY c.temporada_id, c.ordem, c.id, r.chegada
        ", [$userId, $userId]);

        $idsPilotos = $idsEquipes = $idsPistas = $idsEventos = [];
        $porId = [];
        foreach ($linhas as $l) {
            if (!isset($porId[$l->corrida_id])) {
                $porId[$l->corrida_id] = count($this->corridas);
                $this->corridas[] = [
                    'id' => (int) $l->corrida_id,
                    'temporada_id' => (int) $l->temporada_id,
                    'pista_id' => (int) $l->pista_id,
                    'evento_id' => $l->evento_id ? (int) $l->evento_id : null,
                    'volta_rapida' => $l->volta_rapida ? (int) $l->volta_rapida : null,
                    'res' => [],
                ];
            }
            $this->corridas[$porId[$l->corrida_id]]['res'][] = [
                'pe' => (int) $l->pe,
                'piloto' => (int) $l->piloto_id,
                'equipe' => (int) $l->equipe_id,
                'largada' => (int) $l->largada,
                'chegada' => (int) $l->chegada,
                'abandono' => $l->flg_abandono === 'S',
            ];
            $idsPilotos[$l->piloto_id] = true;
            $idsEquipes[$l->equipe_id] = true;
            $idsPistas[$l->pista_id] = true;
            if ($l->evento_id) {
                $idsEventos[$l->evento_id] = true;
            }
        }

        $this->pilotos = DB::table('pilotos')->whereIn('id', array_keys($idsPilotos))
            ->get(['id', 'nome', 'sobrenome', 'imagem', 'pais_id', 'flg_ativo'])->keyBy('id');
        $this->equipes = DB::table('equipes')->whereIn('id', array_keys($idsEquipes))
            ->get(['id', 'nome', 'imagem', 'des_cor', 'pais_id', 'flg_ativo'])->keyBy('id');
        $this->pistas = DB::table('pistas')->whereIn('id', array_keys($idsPistas))
            ->get(['id', 'nome', 'pais_id'])->keyBy('id');
        $this->eventos = DB::table('eventos')->whereIn('id', array_keys($idsEventos))
            ->get(['id', 'des_nome'])->keyBy('id');
        $this->paises = DB::table('paises')->get(['id', 'des_nome', 'imagem', 'continente_id'])->keyBy('id');
        $this->continentes = DB::table('continentes')->get(['id', 'nome'])->keyBy('id');
        $this->temporadas = DB::table('temporadas')->where('user_id', $userId)
            ->get(['id', 'des_temporada', 'referencia'])->keyBy('id');
    }

    // =====================================================================================
    // FORMATADORES (o que vai para a view)
    // =====================================================================================

    private function piloto(int $id): array
    {
        if (isset($this->cachePiloto[$id])) {
            return $this->cachePiloto[$id];
        }
        $p = $this->pilotos[$id] ?? null;
        $pais = $p ? ($this->paises[$p->pais_id] ?? null) : null;

        return $this->cachePiloto[$id] = [
            'tipo' => 'piloto',
            'id' => $id,
            'nome' => $p ? trim($p->nome.' '.$p->sobrenome) : 'Piloto #'.$id,
            'img' => ImageUrl::for($p->imagem ?? null),
            'bandeira' => $pais && $pais->imagem ? ImageUrl::for($pais->imagem) : null,
            'pais' => $pais->des_nome ?? '',
            'ativo' => $p && $p->flg_ativo === 'S',
            'url' => route('pilotos.show', $id),
        ];
    }

    private function equipe(int $id): array
    {
        if (isset($this->cacheEquipe[$id])) {
            return $this->cacheEquipe[$id];
        }
        $e = $this->equipes[$id] ?? null;
        $pais = $e ? ($this->paises[$e->pais_id] ?? null) : null;

        return $this->cacheEquipe[$id] = [
            'tipo' => 'equipe',
            'id' => $id,
            'nome' => $e->nome ?? 'Equipe #'.$id,
            'img' => ImageUrl::for($e->imagem ?? null),
            'cor' => $e ? Equipe::corFundoIcone($e->des_cor, $e->imagem) : '#adb5bd',
            'bandeira' => $pais && $pais->imagem ? ImageUrl::for($pais->imagem) : null,
            'pais' => $pais->des_nome ?? '',
            'ativo' => $e && $e->flg_ativo === 'S',
            'url' => route('equipes.show', $id),
        ];
    }

    private function pais(?int $id): array
    {
        $p = $id ? ($this->paises[$id] ?? null) : null;

        return [
            'id' => $id,
            'nome' => $p->des_nome ?? '—',
            'bandeira' => $p && $p->imagem ? ImageUrl::for($p->imagem) : null,
        ];
    }

    private function temporadaNome(int $id, bool $curto = false): string
    {
        $t = $this->temporadas[$id] ?? null;
        if (!$t) {
            return 'Temporada #'.$id;
        }
        if ($curto) {
            return trim(str_ireplace('Temporada', '', $t->des_temporada)).($t->referencia ? ' · '.$t->referencia : '');
        }

        return $t->des_temporada.($t->referencia ? ' ('.$t->referencia.')' : '');
    }

    /** Referência curta de uma corrida (pelo índice cronológico) */
    private function corrida(int $idx): array
    {
        $c = $this->corridas[$idx];
        $pista = $this->pistas[$c['pista_id']] ?? null;
        $evento = $c['evento_id'] ? ($this->eventos[$c['evento_id']] ?? null) : null;

        return [
            'id' => $c['id'],
            'evento' => $evento->des_nome ?? ($pista->nome ?? 'GP'),
            'pista' => $pista->nome ?? '',
            'temporada' => $this->temporadaNome($c['temporada_id']),
            'bandeira' => $this->pais($pista->pais_id ?? null)['bandeira'],
            'url' => route('resultados.show', $c['id']),
        ];
    }

    private function paisDaPista(int $pistaId): ?int
    {
        return isset($this->pistas[$pistaId]) ? (int) $this->pistas[$pistaId]->pais_id : null;
    }

    private function continenteDoPais(?int $paisId): ?int
    {
        $p = $paisId ? ($this->paises[$paisId] ?? null) : null;

        return $p && $p->continente_id ? (int) $p->continente_id : null;
    }

    private function nomeContinente(?int $id): string
    {
        $nome = $id && isset($this->continentes[$id]) ? $this->continentes[$id]->nome : 'Sem continente';

        return self::CONTINENTES[$nome] ?? $nome;
    }

    /**
     * Ordena e corta um ranking guardando os LIMITE primeiros no geral e também os LIMITE primeiros
     * só entre os ativos (a tela tem o filtro "apenas em atividade").
     */
    private function ranking(array $itens, callable $comparador): array
    {
        usort($itens, $comparador);
        $saida = [];
        $ativos = 0;
        foreach ($itens as $i => $item) {
            $ativo = $item['quem']['ativo'] ?? false;
            if ($i < self::LIMITE || ($ativo && $ativos < self::LIMITE)) {
                $saida[] = $item;
            }
            if ($ativo) {
                $ativos++;
            }
        }

        return $saida;
    }

    private static function porValor(string $campo = 'valor'): callable
    {
        return fn ($a, $b) => [$b[$campo], $b['desempate'] ?? 0] <=> [$a[$campo], $a['desempate'] ?? 0];
    }

    private static function novaSequencia(): array
    {
        return ['atual' => 0, 'inicio' => null, 'max' => 0, 'maxInicio' => null, 'maxFim' => null];
    }

    private static function passo(array &$s, bool $ok, int $idx): void
    {
        if (!$ok) {
            $s['atual'] = 0;

            return;
        }
        if ($s['atual'] === 0) {
            $s['inicio'] = $idx;
        }
        $s['atual']++;
        if ($s['atual'] > $s['max']) {
            $s['max'] = $s['atual'];
            $s['maxInicio'] = $s['inicio'];
            $s['maxFim'] = $idx;
        }
    }

    private function periodo(?int $ini, ?int $fim): string
    {
        if ($ini === null) {
            return '';
        }
        $a = $this->corrida($ini);
        if ($fim === null || $fim === $ini) {
            return $a['evento'].' · '.$a['temporada'];
        }
        $b = $this->corrida($fim);
        $tA = $this->corridas[$ini]['temporada_id'];
        $tB = $this->corridas[$fim]['temporada_id'];
        if ($tA === $tB) {
            return $a['evento'].' → '.$b['evento'].' · '.$a['temporada'];
        }

        return $a['evento'].' ('.$this->temporadaNome($tA, true).') → '.$b['evento'].' ('.$this->temporadaNome($tB, true).')';
    }

    /** "30,2%" (sem ",0" quando é inteiro) */
    private static function pctTxt($parte, $total): string
    {
        return str_replace(',0', '', number_format(self::pct($parte, $total), 1, ',', '.')).'%';
    }

    /** "1 vitória" / "3 vitórias" */
    private static function qtd($n, string $singular, string $plural): string
    {
        return number_format($n, 0, ',', '.').' '.((int) $n === 1 ? $singular : $plural);
    }

    private static function pct($parte, $total, int $casas = 1): float
    {
        return $total > 0 ? round($parte * 100 / $total, $casas) : 0;
    }

    // =====================================================================================
    // CÁLCULO
    // =====================================================================================

    private function calcular(int $userId): array
    {
        $inicio = microtime(true);
        $this->carregar($userId);
        $totalCorridas = count($this->corridas);

        $P = [];   // acumuladores por piloto
        $E = [];   // acumuladores por equipe
        $T = [];   // por temporada
        $pistasAc = [];
        $continentesAc = [];
        $paisesSede = [];
        $pilotoPista = [];
        $temporadaPiloto = [];
        $trios = [];
        $escaladas = [];
        $quedas = [];
        $vitoriasDeTras = [];
        $abandonosPorCorrida = [];
        $semAbandono = [];
        $gridVencedor = [];
        $poleVenceu = 0;
        $polesAbandonaram = 0;
        $hatTricksTotal = 0;
        $totalAbandonos = 0;
        $sequenciaEquipe = ['equipe' => null, 'atual' => 0, 'inicio' => null];
        $recordesEquipe = [];
        $vencedoresDistintos = [];
        $polesDistintos = [];

        foreach ($this->corridas as $idx => $c) {
            $paisCorrida = $this->paisDaPista($c['pista_id']);
            $contCorrida = $this->continenteDoPais($paisCorrida);
            $tid = $c['temporada_id'];

            $T[$tid] ??= ['corridas' => 0, 'abandonos' => 0, 'vencedores' => [], 'poleVenceu' => 0, 'equipes' => []];
            $T[$tid]['corridas']++;
            $pistasAc[$c['pista_id']] ??= ['corridas' => 0, 'abandonos' => 0, 'vencedores' => []];
            $pistasAc[$c['pista_id']]['corridas']++;
            $continentesAc[$contCorrida ?? 0] ??= ['corridas' => 0, 'pilotos' => [], 'equipes' => [], 'paises' => []];
            $continentesAc[$contCorrida ?? 0]['corridas']++;
            $paisesSede[$paisCorrida ?? 0] ??= ['corridas' => 0, 'locais' => 0, 'vencedoresLocais' => []];
            $paisesSede[$paisCorrida ?? 0]['corridas']++;

            $vencedor = $pole = null;
            $abandonos = 0;
            $porChegada = [];
            $equipesNaCorrida = [];

            foreach ($c['res'] as $r) {
                $pid = $r['piloto'];
                $eid = $r['equipe'];
                $piloto = $this->pilotos[$pid] ?? null;
                $emCasa = $piloto && $paisCorrida && (int) $piloto->pais_id === $paisCorrida;
                $venceu = $r['chegada'] === 1;
                $fezPole = $r['largada'] === 1;
                $podio = $r['chegada'] >= 1 && $r['chegada'] <= 3;
                $top10 = $r['chegada'] >= 1 && $r['chegada'] <= 10;

                if ($venceu) {
                    $vencedor = $r;
                }
                if ($fezPole) {
                    $pole = $r;
                }
                if ($r['abandono']) {
                    $abandonos++;
                }
                $porChegada[$r['chegada']] = $r;

                // ---------- piloto ----------
                $P[$pid] ??= [
                    'corridas' => 0, 'vitorias' => 0, 'poles' => 0, 'podios' => 0, 'abandonos' => 0, 'hat' => 0,
                    'ultVit' => null, 'ultPole' => null, 'ultPodio' => null,
                    'desdeVit' => 0, 'desdePole' => 0, 'desdePodio' => 0,
                    'maiorJejum' => 0, 'jejumDe' => null, 'jejumAte' => null, 'nPrimeiraVit' => null,
                    'seq' => ['vit' => self::novaSequencia(), 'pole' => self::novaSequencia(), 'podio' => self::novaSequencia(),
                              'top10' => self::novaSequencia(), 'semAband' => self::novaSequencia()],
                    'equipes' => [], 'equipesVit' => [], 'temporadas' => [], 'contVit' => [],
                    'casa' => ['corridas' => 0, 'vitorias' => 0, 'podios' => 0, 'somaChegada' => 0, 'chegadas' => 0],
                    'fora' => ['corridas' => 0, 'vitorias' => 0, 'somaChegada' => 0, 'chegadas' => 0],
                    'primeira' => $idx, 'ultima' => $idx,
                ];
                $p = &$P[$pid];
                $p['corridas']++;
                $p['ultima'] = $idx;
                $p['equipes'][$eid] = ($p['equipes'][$eid] ?? 0) + 1;
                $p['temporadas'][$tid] = true;

                if ($venceu) {
                    if ($p['ultVit'] !== null && $p['desdeVit'] > $p['maiorJejum']) {
                        $p['maiorJejum'] = $p['desdeVit'];
                        $p['jejumDe'] = $p['ultVit'];
                        $p['jejumAte'] = $idx;
                    }
                    if ($p['nPrimeiraVit'] === null) {
                        $p['nPrimeiraVit'] = $p['corridas'];
                        $p['idxPrimeiraVit'] = $idx;
                    }
                    $p['vitorias']++;
                    $p['ultVit'] = $idx;
                    $p['desdeVit'] = 0;
                    $p['equipesVit'][$eid] = ($p['equipesVit'][$eid] ?? 0) + 1;
                    $p['contVit'][$contCorrida ?? 0] = ($p['contVit'][$contCorrida ?? 0] ?? 0) + 1;
                } else {
                    $p['desdeVit']++;
                }
                if ($fezPole) {
                    $p['poles']++;
                    $p['ultPole'] = $idx;
                    $p['desdePole'] = 0;
                } else {
                    $p['desdePole']++;
                }
                if ($podio) {
                    $p['podios']++;
                    $p['ultPodio'] = $idx;
                    $p['desdePodio'] = 0;
                } else {
                    $p['desdePodio']++;
                }
                if ($r['abandono']) {
                    $p['abandonos']++;
                }
                self::passo($p['seq']['vit'], $venceu, $idx);
                self::passo($p['seq']['pole'], $fezPole, $idx);
                self::passo($p['seq']['podio'], $podio, $idx);
                self::passo($p['seq']['top10'], $top10, $idx);
                self::passo($p['seq']['semAband'], !$r['abandono'], $idx);

                $lado = $emCasa ? 'casa' : 'fora';
                $p[$lado]['corridas']++;
                if ($venceu) {
                    $p[$lado]['vitorias']++;
                }
                if ($emCasa && $podio) {
                    $p['casa']['podios']++;
                }
                if (!$r['abandono']) {
                    $p[$lado]['somaChegada'] += $r['chegada'];
                    $p[$lado]['chegadas']++;
                }
                unset($p);

                // ---------- equipe (uma vez por corrida) ----------
                $E[$eid] ??= ['corridas' => 0, 'vitorias' => 0, 'poles' => 0, 'ultVit' => null, 'desdeVit' => 0,
                              'pilotos' => [], 'dobradinhas' => 0, 'vitCasa' => 0, 'temporadas' => []];
                $E[$eid]['pilotos'][$pid] = true;
                $E[$eid]['temporadas'][$tid] = true;
                if (!isset($equipesNaCorrida[$eid])) {
                    $equipesNaCorrida[$eid] = true;
                    $E[$eid]['corridas']++;
                }

                // ---------- escaladas / quedas ----------
                if (!$r['abandono'] && $r['largada'] > 0) {
                    $ganho = $r['largada'] - $r['chegada'];
                    if ($ganho >= 8) {
                        $escaladas[] = ['idx' => $idx, 'piloto' => $pid, 'equipe' => $eid, 'largada' => $r['largada'], 'chegada' => $r['chegada'], 'valor' => $ganho];
                    } elseif ($ganho <= -8) {
                        $quedas[] = ['idx' => $idx, 'piloto' => $pid, 'equipe' => $eid, 'largada' => $r['largada'], 'chegada' => $r['chegada'], 'valor' => -$ganho];
                    }
                }
            }

            // ---------- nível da corrida ----------
            $carros = count($c['res']);
            $totalAbandonos += $abandonos;
            $T[$tid]['abandonos'] += $abandonos;
            $pistasAc[$c['pista_id']]['abandonos'] += $abandonos;
            $abandonosPorCorrida[] = ['idx' => $idx, 'valor' => $abandonos, 'carros' => $carros];
            if ($abandonos === 0) {
                $semAbandono[] = $idx;
            }
            foreach (array_keys($equipesNaCorrida) as $eid) {
                $T[$tid]['equipes'][$eid] = true;
            }

            if ($pole && $pole['abandono']) {
                $polesAbandonaram++;
            }
            if ($pole) {
                $polesDistintos[$pole['piloto']] = true;
                $E[$pole['equipe']]['poles']++;
            }

            if ($vencedor) {
                $vp = $vencedor['piloto'];
                $ve = $vencedor['equipe'];
                $vencedoresDistintos[$vp] = true;
                $T[$tid]['vencedores'][$vp] = ($T[$tid]['vencedores'][$vp] ?? 0) + 1;
                $pistasAc[$c['pista_id']]['vencedores'][$vp] = true;
                $continentesAc[$contCorrida ?? 0]['pilotos'][$vp] = ($continentesAc[$contCorrida ?? 0]['pilotos'][$vp] ?? 0) + 1;
                $continentesAc[$contCorrida ?? 0]['equipes'][$ve] = ($continentesAc[$contCorrida ?? 0]['equipes'][$ve] ?? 0) + 1;
                $paisVencedor = isset($this->pilotos[$vp]) ? (int) $this->pilotos[$vp]->pais_id : 0;
                $continentesAc[$contCorrida ?? 0]['paises'][$paisVencedor] = ($continentesAc[$contCorrida ?? 0]['paises'][$paisVencedor] ?? 0) + 1;
                if ($paisCorrida && $paisVencedor === $paisCorrida) {
                    $paisesSede[$paisCorrida]['locais']++;
                    $paisesSede[$paisCorrida]['vencedoresLocais'][$vp] = ($paisesSede[$paisCorrida]['vencedoresLocais'][$vp] ?? 0) + 1;
                }
                $pilotoPista[$vp.'-'.$c['pista_id']] = ($pilotoPista[$vp.'-'.$c['pista_id']] ?? 0) + 1;
                $temporadaPiloto[$tid.'-'.$vp] = ['t' => $tid, 'p' => $vp, 'e' => $ve, 'v' => ($temporadaPiloto[$tid.'-'.$vp]['v'] ?? 0) + 1];

                // equipe
                $eq = &$E[$ve];
                $eq['vitorias']++;
                $eq['ultVit'] = $idx;
                $equipeVenc = $this->equipes[$ve] ?? null;
                if ($equipeVenc && $paisCorrida && (int) $equipeVenc->pais_id === $paisCorrida) {
                    $eq['vitCasa']++;
                }
                unset($eq);

                // grid de largada do vencedor
                $gridVencedor[] = $vencedor['largada'];
                if ($vencedor['largada'] === 1) {
                    $poleVenceu++;
                    $T[$tid]['poleVenceu']++;
                }
                if ($vencedor['largada'] >= 2) {
                    $vitoriasDeTras[] = ['idx' => $idx, 'piloto' => $vp, 'equipe' => $ve, 'valor' => $vencedor['largada']];
                }

                // hat-trick: pole + vitória + volta mais rápida
                if ($pole && $pole['pe'] === $vencedor['pe'] && $c['volta_rapida'] === $vencedor['pe']) {
                    $P[$vp]['hat']++;
                    $hatTricksTotal++;
                }

                // dobradinha
                if (isset($porChegada[2]) && $porChegada[2]['equipe'] === $ve) {
                    $E[$ve]['dobradinhas']++;
                }

                // trio do pódio (sem ordem)
                if (isset($porChegada[2], $porChegada[3])) {
                    $trio = [$vp, $porChegada[2]['piloto'], $porChegada[3]['piloto']];
                    sort($trio);
                    $k = implode('-', $trio);
                    $trios[$k] = ['pilotos' => $trio, 'valor' => ($trios[$k]['valor'] ?? 0) + 1, 'ultima' => $idx];
                }

                // vitórias consecutivas de uma equipe no calendário
                if ($sequenciaEquipe['equipe'] === $ve) {
                    $sequenciaEquipe['atual']++;
                } else {
                    $sequenciaEquipe = ['equipe' => $ve, 'atual' => 1, 'inicio' => $idx];
                }
                $chaveSeq = $ve.'-'.$sequenciaEquipe['inicio'];
                if ($sequenciaEquipe['atual'] >= 3) {
                    $recordesEquipe[$chaveSeq] = ['equipe' => $ve, 'valor' => $sequenciaEquipe['atual'], 'inicio' => $sequenciaEquipe['inicio'], 'fim' => $idx];
                }
            }

            // equipes que não venceram: jejum em GPs disputados
            foreach (array_keys($equipesNaCorrida) as $eid) {
                if (!$vencedor || $vencedor['equipe'] !== $eid) {
                    $E[$eid]['desdeVit']++;
                } else {
                    $E[$eid]['desdeVit'] = 0;
                }
            }
        }

        $dados = [
            'gerado_em' => now()->format('d/m/Y H:i'),
            'tempo_ms' => 0,
            'totalCorridas' => $totalCorridas,
        ];

        $dados['kpis'] = $this->kpis($P, $E, $T, $totalCorridas, $totalAbandonos, $poleVenceu, $polesAbandonaram, $hatTricksTotal, count($semAbandono), count($vencedoresDistintos), count($polesDistintos));
        $dados['jejuns'] = $this->jejuns($P, $E, $totalCorridas);
        $dados['sequencias'] = $this->sequencias($P, $recordesEquipe);
        $dados['casa'] = $this->emCasa($P, $E, $paisesSede);
        $dados['continentes'] = $this->continentes($P, $continentesAc);
        $dados['abandonos'] = $this->abandonos($P, $T, $pistasAc, $abandonosPorCorrida, $semAbandono, $totalCorridas);
        $dados['carreira'] = $this->carreira($P, $E);
        $dados['corridas'] = $this->recordesCorrida($P, $E, $escaladas, $quedas, $vitoriasDeTras, $gridVencedor, $pilotoPista, $trios);
        $dados['temporadas'] = $this->temporadasDados($T, $temporadaPiloto, $userId);

        $dados['tempo_ms'] = (int) round((microtime(true) - $inicio) * 1000);

        return $dados;
    }

    // =====================================================================================
    // BLOCOS
    // =====================================================================================

    private function kpis($P, $E, $T, $totalCorridas, $totalAbandonos, $poleVenceu, $polesAbandonaram, $hat, $semAbandono, $vencedores, $poles): array
    {
        return [
            ['rotulo' => 'Grandes Prêmios', 'valor' => $totalCorridas, 'icone' => 'bi-flag-fill', 'nota' => count($T).' temporadas'],
            ['rotulo' => 'Pilotos no grid', 'valor' => count($P), 'icone' => 'bi-person-fill', 'nota' => count($E).' equipes'],
            ['rotulo' => 'Vencedores diferentes', 'valor' => $vencedores, 'icone' => 'bi-trophy-fill', 'nota' => $poles.' pole-sitters diferentes'],
            ['rotulo' => 'Vitórias da pole', 'valor' => number_format(self::pct($poleVenceu, $totalCorridas), 1, ',', '.').'%', 'icone' => 'bi-stopwatch-fill', 'nota' => $poleVenceu.' de '.number_format($totalCorridas, 0, ',', '.').' GPs'],
            ['rotulo' => 'Abandonos', 'valor' => $totalAbandonos, 'icone' => 'bi-exclamation-octagon-fill', 'nota' => number_format($totalCorridas ? $totalAbandonos / $totalCorridas : 0, 1, ',', '.').' por GP'],
            ['rotulo' => 'GPs sem abandonos', 'valor' => $semAbandono, 'icone' => 'bi-shield-check', 'nota' => number_format(self::pct($semAbandono, $totalCorridas), 1, ',', '.').'% das corridas'],
            ['rotulo' => 'Hat-tricks', 'valor' => $hat, 'icone' => 'bi-stars', 'nota' => 'pole + vitória + volta rápida'],
            ['rotulo' => 'Poles que abandonaram', 'valor' => $polesAbandonaram, 'icone' => 'bi-x-octagon-fill', 'nota' => 'largou em 1º e não terminou'],
        ];
    }

    private function jejuns(array $P, array $E, int $totalCorridas): array
    {
        $nuncaVenceu = $nuncaPole = $nuncaPodio = $maiorJejum = $ateVitoria = $podiosSemVitoria = [];
        $jejumAtual = ['vit' => [], 'pole' => [], 'podio' => []];
        $campos = ['vit' => ['ultVit', 'desdeVit', 'Última vitória'], 'pole' => ['ultPole', 'desdePole', 'Última pole'], 'podio' => ['ultPodio', 'desdePodio', 'Último pódio']];

        foreach ($P as $pid => $p) {
            $quem = $this->piloto($pid);
            foreach ($campos as $chave => [$ult, $desde, $nome]) {
                if ($p[$ult] !== null && $p[$desde] > 0) {
                    $ref = $this->corrida($p[$ult]);
                    $jejumAtual[$chave][] = [
                        'quem' => $quem, 'valor' => $p[$desde], 'desempate' => $totalCorridas - $p[$ult],
                        'detalhe' => $nome.': '.$ref['evento'].' · '.$ref['temporada'],
                        'extra' => ($totalCorridas - 1 - $p[$ult]).' GPs no calendário desde então',
                        'link' => $ref['url'],
                    ];
                }
            }

            $base = ['quem' => $quem, 'desempate' => $p['podios']];
            if ($p['vitorias'] === 0) {
                $nuncaVenceu[] = $base + ['valor' => $p['corridas'], 'detalhe' => self::qtd($p['podios'], 'pódio', 'pódios').' · '.self::qtd($p['poles'], 'pole', 'poles').''];
                if ($p['podios'] > 0) {
                    $podiosSemVitoria[] = ['quem' => $quem, 'valor' => $p['podios'], 'desempate' => -$p['corridas'], 'detalhe' => 'em '.self::qtd($p['corridas'], 'GP', 'GPs').', sem vencer'];
                }
            }
            if ($p['poles'] === 0) {
                $nuncaPole[] = ['quem' => $quem, 'valor' => $p['corridas'], 'desempate' => $p['vitorias'], 'detalhe' => self::qtd($p['vitorias'], 'vitória', 'vitórias').' sem nunca largar na frente'];
            }
            if ($p['podios'] === 0) {
                $nuncaPodio[] = ['quem' => $quem, 'valor' => $p['corridas'], 'desempate' => 0, 'detalhe' => 'em '.self::qtd(count($p['temporadas']), 'temporada', 'temporadas').''];
            }
            if ($p['maiorJejum'] > 0) {
                $maiorJejum[] = ['quem' => $quem, 'valor' => $p['maiorJejum'], 'detalhe' => $this->periodo($p['jejumDe'], $p['jejumAte'])];
            }
            if ($p['nPrimeiraVit'] !== null && $p['nPrimeiraVit'] > 1) {
                $ref = $this->corrida($p['idxPrimeiraVit']);
                $ateVitoria[] = ['quem' => $quem, 'valor' => $p['nPrimeiraVit'], 'detalhe' => 'Venceu pela 1ª vez no '.$ref['evento'].' · '.$ref['temporada'], 'link' => $ref['url']];
            }
        }

        $equipesSemVencer = [];
        foreach ($E as $eid => $e) {
            if ($e['ultVit'] !== null && $e['desdeVit'] > 0) {
                $ref = $this->corrida($e['ultVit']);
                $equipesSemVencer[] = ['quem' => $this->equipe($eid), 'valor' => $e['desdeVit'], 'desempate' => -$e['ultVit'],
                    'detalhe' => 'Última vitória: '.$ref['evento'].' · '.$ref['temporada'], 'link' => $ref['url']];
            }
        }

        $v = self::porValor();

        return [
            'semVencer' => $this->ranking($jejumAtual['vit'], $v),
            'semPole' => $this->ranking($jejumAtual['pole'], $v),
            'semPodio' => $this->ranking($jejumAtual['podio'], $v),
            'nuncaVenceu' => $this->ranking($nuncaVenceu, $v),
            'nuncaPole' => $this->ranking($nuncaPole, $v),
            'nuncaPodio' => $this->ranking($nuncaPodio, $v),
            'podiosSemVitoria' => $this->ranking($podiosSemVitoria, $v),
            'maiorJejum' => $this->ranking($maiorJejum, $v),
            'ateVitoria' => $this->ranking($ateVitoria, $v),
            'equipesSemVencer' => $this->ranking($equipesSemVencer, $v),
        ];
    }

    private function sequencias(array $P, array $recordesEquipe): array
    {
        $tipos = ['vit' => 'vitórias', 'pole' => 'poles', 'podio' => 'pódios', 'top10' => 'top 10', 'semAband' => 'GPs sem abandonar'];
        $listas = array_fill_keys(array_keys($tipos), []);
        foreach ($P as $pid => $p) {
            foreach ($tipos as $tipo => $nome) {
                $s = $p['seq'][$tipo];
                if ($s['max'] >= 2) {
                    $emAndamento = $s['atual'] === $s['max'] && $s['maxFim'] === $p['ultima'];
                    $listas[$tipo][] = [
                        'quem' => $this->piloto($pid), 'valor' => $s['max'], 'desempate' => -$s['maxInicio'],
                        'detalhe' => $this->periodo($s['maxInicio'], $s['maxFim']),
                        'tag' => $emAndamento ? 'em andamento' : null,
                    ];
                }
            }
        }

        $equipes = [];
        foreach ($recordesEquipe as $r) {
            $equipes[] = ['quem' => $this->equipe($r['equipe']), 'valor' => $r['valor'], 'desempate' => -$r['inicio'], 'detalhe' => $this->periodo($r['inicio'], $r['fim'])];
        }

        $v = self::porValor();
        $saida = [];
        foreach ($listas as $tipo => $lista) {
            $saida[$tipo] = $this->ranking($lista, $v);
        }
        $saida['equipes'] = array_slice($this->ranking($equipes, $v), 0, self::LIMITE);

        return $saida;
    }

    private function emCasa(array $P, array $E, array $paisesSede): array
    {
        $vitorias = $podios = $nunca = $fator = $pressao = [];

        foreach ($P as $pid => $p) {
            $quem = $this->piloto($pid);
            $c = $p['casa'];
            if ($c['vitorias'] > 0) {
                $vitorias[] = ['quem' => $quem, 'valor' => $c['vitorias'], 'desempate' => -$c['corridas'],
                    'detalhe' => self::qtd($c['corridas'], 'GP', 'GPs').' em casa · '.self::pctTxt($c['vitorias'], $c['corridas']).' de aproveitamento'];
            }
            if ($c['podios'] > 0) {
                $podios[] = ['quem' => $quem, 'valor' => $c['podios'], 'desempate' => -$c['corridas'], 'detalhe' => 'em '.self::qtd($c['corridas'], 'GP', 'GPs').' em casa'];
            }
            if ($c['corridas'] === 0) {
                $paisId = isset($this->pilotos[$pid]) ? (int) $this->pilotos[$pid]->pais_id : 0;
                $sediados = $paisesSede[$paisId]['corridas'] ?? 0;
                $nunca[] = ['quem' => $quem, 'valor' => $p['corridas'], 'desempate' => $p['vitorias'],
                    'detalhe' => $sediados === 0
                        ? ($quem['pais'] ?: 'O país dele').' nunca recebeu um GP'
                        : ($quem['pais'] ?: 'O país').' recebeu '.$sediados.' GP'.($sediados > 1 ? 's' : '').', nenhum com ele no grid',
                    'tag' => $sediados === 0 ? 'sem GP no país' : null];
            }
            if ($c['chegadas'] >= 3 && $p['fora']['chegadas'] >= 10) {
                $mediaCasa = $c['somaChegada'] / $c['chegadas'];
                $mediaFora = $p['fora']['somaChegada'] / $p['fora']['chegadas'];
                $diff = round($mediaFora - $mediaCasa, 1);
                $item = ['quem' => $quem, 'valor' => abs($diff), 'desempate' => $c['chegadas'],
                    'detalhe' => 'Média de chegada: '.number_format($mediaCasa, 1, ',', '').'º em casa × '.number_format($mediaFora, 1, ',', '').'º fora ('.self::qtd($c['chegadas'], 'GP', 'GPs').' em casa)'];
                if ($diff > 0) {
                    $fator[] = $item;
                } elseif ($diff < 0) {
                    $pressao[] = $item;
                }
            }
        }

        $equipes = [];
        foreach ($E as $eid => $e) {
            if ($e['vitCasa'] > 0) {
                $equipes[] = ['quem' => $this->equipe($eid), 'valor' => $e['vitCasa'], 'detalhe' => self::pctTxt($e['vitCasa'], $e['vitorias']).' das '.self::qtd($e['vitorias'], 'vitória', 'vitórias').' da equipe'];
            }
        }

        $paises = [];
        foreach ($paisesSede as $paisId => $s) {
            if (!$paisId) {
                continue;
            }
            arsort($s['vencedoresLocais']);
            $paises[] = [
                'pais' => $this->pais($paisId),
                'corridas' => $s['corridas'],
                'locais' => $s['locais'],
                'pct' => self::pct($s['locais'], $s['corridas']),
                'herois' => array_map(fn ($pid) => $this->piloto($pid) + ['vitorias' => $s['vencedoresLocais'][$pid]], array_slice(array_keys($s['vencedoresLocais']), 0, 3)),
            ];
        }
        usort($paises, fn ($a, $b) => [$b['corridas'], $b['locais']] <=> [$a['corridas'], $a['locais']]);

        $v = self::porValor();

        return [
            'vitorias' => $this->ranking($vitorias, $v),
            'podios' => $this->ranking($podios, $v),
            'nunca' => $this->ranking($nunca, $v),
            'nuncaTotal' => count($nunca),
            'fator' => $this->ranking($fator, $v),
            'pressao' => $this->ranking($pressao, $v),
            'equipes' => $this->ranking($equipes, $v),
            'paises' => $paises,
        ];
    }

    private function continentes(array $P, array $continentesAc): array
    {
        // GPs / vitórias por continente onde a corrida aconteceu
        $porSede = [];
        foreach ($continentesAc as $contId => $c) {
            arsort($c['pilotos']);
            arsort($c['equipes']);
            arsort($c['paises']);
            $topE = array_key_first($c['equipes']);
            $topPais = array_key_first($c['paises']);
            $porSede[] = [
                'id' => $contId,
                'nome' => $this->nomeContinente($contId ?: null),
                'corridas' => $c['corridas'],
                'vencedores' => count($c['pilotos']),
                'topPilotos' => array_map(fn ($pid) => $this->piloto($pid) + ['vitorias' => $c['pilotos'][$pid]], array_slice(array_keys($c['pilotos']), 0, 3)),
                'topEquipe' => $topE ? $this->equipe($topE) + ['vitorias' => $c['equipes'][$topE]] : null,
                'topPais' => $topPais ? $this->pais($topPais) + ['vitorias' => $c['paises'][$topPais]] : null,
            ];
        }
        usort($porSede, fn ($a, $b) => $b['corridas'] <=> $a['corridas']);

        // vitórias pelo continente de nascimento do piloto
        $porOrigem = [];
        foreach ($P as $pid => $p) {
            if ($p['vitorias'] === 0) {
                continue;
            }
            $cont = $this->continenteDoPais(isset($this->pilotos[$pid]) ? (int) $this->pilotos[$pid]->pais_id : null);
            $nome = $this->nomeContinente($cont);
            $porOrigem[$nome] ??= ['nome' => $nome, 'vitorias' => 0, 'pilotos' => 0];
            $porOrigem[$nome]['vitorias'] += $p['vitorias'];
            $porOrigem[$nome]['pilotos']++;
        }
        usort($porOrigem, fn ($a, $b) => $b['vitorias'] <=> $a['vitorias']);

        // matriz: maiores vencedores x continentes
        $colunas = array_map(fn ($c) => ['id' => $c['id'], 'nome' => $c['nome']], $porSede);
        $vencedores = array_filter($P, fn ($p) => $p['vitorias'] > 0);
        uasort($vencedores, fn ($a, $b) => $b['vitorias'] <=> $a['vitorias']);
        $matriz = [];
        $maxCelula = 0;
        foreach (array_slice($vencedores, 0, 15, true) as $pid => $p) {
            $celulas = [];
            foreach ($colunas as $col) {
                $celulas[] = $n = $p['contVit'][$col['id']] ?? 0;
                $maxCelula = max($maxCelula, $n);
            }
            $matriz[] = ['quem' => $this->piloto($pid), 'total' => $p['vitorias'], 'celulas' => $celulas];
        }

        // vencedores em mais continentes
        $globais = [];
        foreach ($vencedores as $pid => $p) {
            $conts = array_keys(array_filter($p['contVit']));
            if (count($conts) >= 2) {
                $globais[] = ['quem' => $this->piloto($pid), 'valor' => count($conts), 'desempate' => $p['vitorias'],
                    'detalhe' => implode(' · ', array_map(fn ($c) => $this->nomeContinente($c ?: null), $conts))];
            }
        }

        return [
            'porSede' => $porSede,
            'porOrigem' => array_values($porOrigem),
            'colunas' => $colunas,
            'matriz' => $matriz,
            'maxCelula' => $maxCelula,
            'globais' => $this->ranking($globais, self::porValor()),
        ];
    }

    private function abandonos(array $P, array $T, array $pistasAc, array $porCorrida, array $semAbandono, int $totalCorridas): array
    {
        usort($porCorrida, fn ($a, $b) => [$b['valor'], $b['carros']] <=> [$a['valor'], $a['carros']]);
        $recordes = [];
        foreach (array_slice($porCorrida, 0, 15) as $r) {
            $recordes[] = ['corrida' => $this->corrida($r['idx']), 'valor' => $r['valor'], 'carros' => $r['carros'],
                'pct' => self::pct($r['valor'], $r['carros']), 'vencedor' => $this->vencedorDe($r['idx'])];
        }

        $limpas = [];
        foreach (array_reverse($semAbandono) as $idx) {
            $limpas[] = ['corrida' => $this->corrida($idx), 'carros' => count($this->corridas[$idx]['res']), 'vencedor' => $this->vencedorDe($idx)];
        }

        // distribuição: quantos GPs tiveram N abandonos
        $distribuicao = [];
        foreach ($porCorrida as $r) {
            $faixa = min($r['valor'], 12);
            $distribuicao[$faixa] = ($distribuicao[$faixa] ?? 0) + 1;
        }
        ksort($distribuicao);
        $dist = [];
        for ($i = 0; $i <= 12; $i++) {
            $dist[] = ['rotulo' => $i === 12 ? '12+' : (string) $i, 'valor' => $distribuicao[$i] ?? 0];
        }

        $pilotosTotal = $pilotosTaxa = $confiaveis = [];
        foreach ($P as $pid => $p) {
            $quem = $this->piloto($pid);
            if ($p['abandonos'] > 0) {
                $pilotosTotal[] = ['quem' => $quem, 'valor' => $p['abandonos'], 'desempate' => -$p['corridas'], 'detalhe' => 'em '.self::qtd($p['corridas'], 'GP', 'GPs').' ('.self::pctTxt($p['abandonos'], $p['corridas']).')'];
            }
            if ($p['corridas'] >= 20) {
                $taxa = self::pct($p['abandonos'], $p['corridas']);
                $pilotosTaxa[] = ['quem' => $quem, 'valor' => $taxa, 'desempate' => $p['corridas'], 'detalhe' => self::qtd($p['abandonos'], 'abandono', 'abandonos').' em '.self::qtd($p['corridas'], 'GP', 'GPs').'', 'sufixo' => '%'];
                $confiaveis[] = ['quem' => $quem, 'valor' => round(100 - $taxa, 1), 'desempate' => $p['corridas'], 'detalhe' => self::qtd($p['abandonos'], 'abandono', 'abandonos').' em '.self::qtd($p['corridas'], 'GP', 'GPs').'', 'sufixo' => '%'];
            }
        }

        $pistas = [];
        foreach ($pistasAc as $pistaId => $s) {
            if ($s['corridas'] >= 3) {
                $pista = $this->pistas[$pistaId] ?? null;
                $pistas[] = ['pista' => $pista->nome ?? 'Pista #'.$pistaId, 'bandeira' => $this->pais($pista->pais_id ?? null)['bandeira'],
                    'media' => round($s['abandonos'] / $s['corridas'], 1), 'corridas' => $s['corridas'], 'total' => $s['abandonos'],
                    'url' => route('pistas.show', $pistaId)];
            }
        }
        usort($pistas, fn ($a, $b) => [$b['media'], $b['corridas']] <=> [$a['media'], $a['corridas']]);

        $porTemporada = [];
        foreach ($T as $tid => $t) {
            $porTemporada[] = ['rotulo' => $this->temporadaNome($tid, true), 'nome' => $this->temporadaNome($tid), 'media' => round($t['abandonos'] / max(1, $t['corridas']), 2), 'total' => $t['abandonos'], 'corridas' => $t['corridas']];
        }

        $v = self::porValor();

        return [
            'recordes' => $recordes,
            'limpas' => $limpas,
            'limpasPct' => self::pct(count($semAbandono), $totalCorridas),
            'distribuicao' => $dist,
            'pilotosTotal' => $this->ranking($pilotosTotal, $v),
            'pilotosTaxa' => $this->ranking($pilotosTaxa, $v),
            'confiaveis' => $this->ranking($confiaveis, $v),
            'pistasPerigosas' => array_slice($pistas, 0, 12),
            'pistasSeguras' => array_slice(array_reverse($pistas), 0, 12),
            'porTemporada' => $porTemporada,
        ];
    }

    private function vencedorDe(int $idx): ?array
    {
        foreach ($this->corridas[$idx]['res'] as $r) {
            if ($r['chegada'] === 1) {
                return $this->piloto($r['piloto']) + ['equipe' => $this->equipe($r['equipe'])];
            }
        }

        return null;
    }

    private function carreira(array $P, array $E): array
    {
        $maisEquipes = $venceuPor = $temporadas = $corridas = $hat = $umaVitoria = [];
        foreach ($P as $pid => $p) {
            $quem = $this->piloto($pid);
            arsort($p['equipes']);
            $logos = array_map(fn ($e) => $this->equipe($e), array_slice(array_keys($p['equipes']), 0, 10));
            if (count($p['equipes']) >= 2) {
                $maisEquipes[] = ['quem' => $quem, 'valor' => count($p['equipes']), 'desempate' => $p['corridas'], 'detalhe' => self::qtd($p['corridas'], 'GP', 'GPs').' em '.self::qtd(count($p['temporadas']), 'temporada', 'temporadas').'', 'logos' => $logos];
            }
            if (count($p['equipesVit']) >= 2) {
                arsort($p['equipesVit']);
                $venceuPor[] = ['quem' => $quem, 'valor' => count($p['equipesVit']), 'desempate' => $p['vitorias'], 'detalhe' => self::qtd($p['vitorias'], 'vitória', 'vitórias').' no total',
                    'logos' => array_map(fn ($e) => $this->equipe($e) + ['n' => $p['equipesVit'][$e]], array_keys($p['equipesVit']))];
            }
            $temporadas[] = ['quem' => $quem, 'valor' => count($p['temporadas']), 'desempate' => $p['corridas'], 'detalhe' => self::qtd($p['corridas'], 'GP', 'GPs').' · '.$this->periodo($p['primeira'], $p['ultima'])];
            $corridas[] = ['quem' => $quem, 'valor' => $p['corridas'], 'desempate' => $p['vitorias'], 'detalhe' => self::qtd($p['vitorias'], 'vitória', 'vitórias').' · '.self::qtd($p['podios'], 'pódio', 'pódios').' · '.self::qtd($p['poles'], 'pole', 'poles').''];
            if ($p['hat'] > 0) {
                $hat[] = ['quem' => $quem, 'valor' => $p['hat'], 'desempate' => $p['vitorias'], 'detalhe' => self::pctTxt($p['hat'], $p['vitorias']).' das '.self::qtd($p['vitorias'], 'vitória', 'vitórias').''];
            }
            if ($p['vitorias'] === 1) {
                $umaVitoria[] = $quem + ['corrida' => $this->corrida($p['ultVit']), 'corridas' => $p['corridas']];
            }
        }
        usort($umaVitoria, fn ($a, $b) => $b['corridas'] <=> $a['corridas']);

        $equipesPilotos = $dobradinhas = $equipesTemporadas = [];
        foreach ($E as $eid => $e) {
            $quem = $this->equipe($eid);
            $equipesPilotos[] = ['quem' => $quem, 'valor' => count($e['pilotos']), 'desempate' => $e['corridas'], 'detalhe' => 'em '.self::qtd(count($e['temporadas']), 'temporada', 'temporadas').' · '.self::qtd($e['corridas'], 'GP', 'GPs').''];
            if ($e['dobradinhas'] > 0) {
                $dobradinhas[] = ['quem' => $quem, 'valor' => $e['dobradinhas'], 'desempate' => $e['vitorias'], 'detalhe' => self::pctTxt($e['dobradinhas'], $e['vitorias']).' das '.self::qtd($e['vitorias'], 'vitória', 'vitórias').' da equipe'];
            }
        }

        $v = self::porValor();

        return [
            'maisEquipes' => $this->ranking($maisEquipes, $v),
            'venceuPor' => $this->ranking($venceuPor, $v),
            'temporadas' => $this->ranking($temporadas, $v),
            'corridas' => $this->ranking($corridas, $v),
            'hatTricks' => $this->ranking($hat, $v),
            'umaVitoria' => $umaVitoria,
            'equipesPilotos' => $this->ranking($equipesPilotos, $v),
            'dobradinhas' => $this->ranking($dobradinhas, $v),
        ];
    }

    private function recordesCorrida(array $P, array $E, array $escaladas, array $quedas, array $vitoriasDeTras, array $gridVencedor, array $pilotoPista, array $trios): array
    {
        $formatar = function (array $lista, int $n) {
            usort($lista, fn ($a, $b) => [$b['valor'], -$a['idx']] <=> [$a['valor'], -$b['idx']]);
            $saida = [];
            foreach (array_slice($lista, 0, $n) as $i) {
                $saida[] = ['quem' => $this->piloto($i['piloto']), 'equipe' => $this->equipe($i['equipe']), 'valor' => $i['valor'],
                    'largada' => $i['largada'] ?? $i['valor'], 'chegada' => $i['chegada'] ?? 1, 'corrida' => $this->corrida($i['idx'])];
            }

            return $saida;
        };

        // de onde saem os vencedores
        $grid = [];
        foreach ($gridVencedor as $l) {
            $faixa = $l >= 16 ? 16 : max(1, $l);
            $grid[$faixa] = ($grid[$faixa] ?? 0) + 1;
        }
        $gridSaida = [];
        for ($i = 1; $i <= 16; $i++) {
            $gridSaida[] = ['rotulo' => $i === 16 ? 'P16+' : 'P'.$i, 'valor' => $grid[$i] ?? 0];
        }

        // donos da pista
        arsort($pilotoPista);
        $donos = [];
        foreach (array_slice($pilotoPista, 0, 20, true) as $k => $n) {
            [$pid, $pistaId] = array_map('intval', explode('-', $k));
            $pista = $this->pistas[$pistaId] ?? null;
            $donos[] = ['quem' => $this->piloto($pid), 'valor' => $n, 'pista' => $pista->nome ?? '', 'bandeira' => $this->pais($pista->pais_id ?? null)['bandeira'], 'url' => route('pistas.show', $pistaId)];
        }

        // trios de pódio mais repetidos
        usort($trios, fn ($a, $b) => [$b['valor'], $b['ultima']] <=> [$a['valor'], $a['ultima']]);
        $triosSaida = [];
        foreach (array_slice($trios, 0, 8) as $t) {
            $triosSaida[] = ['pilotos' => array_map(fn ($p) => $this->piloto($p), $t['pilotos']), 'valor' => $t['valor'], 'ultima' => $this->corrida($t['ultima'])];
        }

        return [
            'vitoriasDeTras' => $formatar($vitoriasDeTras, 12),
            'escaladas' => $formatar($escaladas, 12),
            'quedas' => $formatar($quedas, 12),
            'gridVencedor' => $gridSaida,
            'donos' => $donos,
            'trios' => $triosSaida,
        ];
    }

    private function temporadasDados(array $T, array $temporadaPiloto, int $userId): array
    {
        $grafico = [];
        $maisDisputadas = [];
        foreach ($T as $tid => $t) {
            $grafico[] = ['rotulo' => $this->temporadaNome($tid, true), 'nome' => $this->temporadaNome($tid), 'vencedores' => count($t['vencedores']), 'corridas' => $t['corridas'],
                'pole' => self::pct($t['poleVenceu'], $t['corridas'])];
            if ($t['corridas'] < 5) {
                continue;
            }
            $vencedoresT = $t['vencedores'];
            arsort($vencedoresT);
            $liderId = array_key_first($vencedoresT);
            $temporadaModel = $this->temporadas[$tid] ?? null;
            $maisDisputadas[] = ['tid' => $tid, 'nome' => $this->temporadaNome($tid), 'vencedores' => count($t['vencedores']), 'corridas' => $t['corridas'],
                'ordinal' => $temporadaModel ? trim(str_ireplace('Temporada', '', $temporadaModel->des_temporada)) : '#'.$tid,
                'referencia' => $temporadaModel->referencia ?? null,
                'url' => route('temporadas.resultados', $tid),
                'lider' => $liderId ? $this->piloto($liderId) + ['vitorias' => $vencedoresT[$liderId]] : null];
        }
        usort($maisDisputadas, fn ($a, $b) => [$b['vencedores'] / max(1, $b['corridas']), $b['vencedores']] <=> [$a['vencedores'] / max(1, $a['corridas']), $a['vencedores']]);

        // domínio: mais vitórias numa temporada
        $dominio = [];
        foreach ($temporadaPiloto as $tp) {
            $corridas = $T[$tp['t']]['corridas'];
            if ($corridas < 5) {
                continue; // temporada ainda no começo: 1 vitória em 1 corrida não é domínio
            }
            $dominio[] = ['quem' => $this->piloto($tp['p']), 'equipe' => $this->equipe($tp['e']), 'valor' => $tp['v'], 'corridas' => $corridas,
                'pct' => self::pct($tp['v'], $corridas), 'temporada' => $this->temporadaNome($tp['t']), 'url' => route('temporadas.resultados', $tp['t'])];
        }
        usort($dominio, fn ($a, $b) => [$b['pct'], $b['valor']] <=> [$a['pct'], $a['valor']]);

        // campeões com menos vitórias
        $campeoes = [];
        $titulos = DB::table('titulos as t')
            ->join('piloto_equipes as pe', 'pe.id', '=', 't.pilotoEquipe_id')
            ->where('t.user_id', $userId)
            ->get(['t.temporada_id', 'pe.piloto_id', 'pe.equipe_id']);
        foreach ($titulos as $t) {
            if (!isset($T[$t->temporada_id])) {
                continue;
            }
            $v = $T[$t->temporada_id]['vencedores'][$t->piloto_id] ?? 0;
            $campeoes[] = ['quem' => $this->piloto((int) $t->piloto_id), 'equipe' => $this->equipe((int) $t->equipe_id), 'valor' => $v,
                'corridas' => $T[$t->temporada_id]['corridas'], 'pct' => self::pct($v, $T[$t->temporada_id]['corridas']),
                'temporada' => $this->temporadaNome((int) $t->temporada_id), 'url' => route('temporadas.resultados', $t->temporada_id)];
        }
        usort($campeoes, fn ($a, $b) => [$a['pct'], $a['valor']] <=> [$b['pct'], $b['valor']]);

        return [
            'grafico' => $grafico,
            'maisDisputadas' => array_slice($maisDisputadas, 0, 8),
            'menosDisputadas' => array_slice(array_reverse($maisDisputadas), 0, 8),
            'dominio' => array_slice($dominio, 0, 12),
            'campeoesMenosVitorias' => array_slice($campeoes, 0, 10),
        ];
    }
}
