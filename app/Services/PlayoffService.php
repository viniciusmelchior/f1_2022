<?php

namespace App\Services;

use App\Models\Site\Corrida;
use App\Models\Site\Temporada;
use Illuminate\Support\Facades\DB;

/**
 * MELHORIA 20261110 (playoff): torneio mata-mata dentro do campeonato (só diversão: não altera
 * a classificação geral, a das sprints nem os pontos).
 *
 * Regras:
 *  - Só corridas principais (sprints ficam de fora), na ordem do calendário (ordem, id).
 *  - qtd_corridas - (corridas de mata-mata) corridas classificatórias; os 8 (ou 16) primeiros da classificação ao fim delas classificam.
 *  - 8 classificados:  quartas 1x8, 4x5, 2x7, 3x6 -> semi -> final.
 *  - 16 classificados: oitavas 1x16, 8x9, 4x13, 5x12, 2x15, 7x10, 3x14, 6x11 -> quartas -> semi -> final.
 *    (chave de torneio: 1 e 2 só se cruzam na final; nas rodadas seguintes os vencedores de confrontos vizinhos se enfrentam)
 *  - Cada fase tem 3 corridas; avança quem somar mais pontos no confronto; empate: avança o melhor cabeça de chave.
 *
 * Tudo é calculado "até a etapa" informada ($ateOrdem), para acompanhar a navegação da tela de classificação.
 */
class PlayoffService
{
    public const FASES = [
        'oitavas' => ['nome' => 'Oitavas de final', 'sigla' => 'OF'],
        'quartas' => ['nome' => 'Quartas de final', 'sigla' => 'QF'],
        'semi' => ['nome' => 'Semifinal', 'sigla' => 'SF'],
        'final' => ['nome' => 'Final', 'sigla' => 'Final'],
    ];

    // cabeças de chave da primeira rodada, na ordem da chave
    private const PRIMEIRA_RODADA = [
        8 => [[1, 8], [4, 5], [2, 7], [3, 6]],
        16 => [[1, 16], [8, 9], [4, 13], [5, 12], [2, 15], [7, 10], [3, 14], [6, 11]],
    ];

    /** chaves das fases de mata-mata da temporada, na ordem */
    public static function fasesDa(Temporada $temporada)
    {
        return $temporada->playoffQtdClassificados() === 16
            ? ['oitavas', 'quartas', 'semi', 'final']
            : ['quartas', 'semi', 'final'];
    }

    /** corridas principais da temporada na ordem do calendário (base de todas as fases) */
    public static function corridasPrincipais($usuario, $temporadaId)
    {
        return Corrida::with(['evento', 'pista'])
            ->where('user_id', $usuario)
            ->where('temporada_id', $temporadaId)
            ->where(fn ($q) => $q->whereNull('flg_sprint')->orWhere('flg_sprint', '<>', 'S'))
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();
    }

    /**
     * [corrida_id => fase] para a listagem de corridas. Não depende de resultados.
     * fase = ['chave', 'nome', 'sigla', 'n', 'total']  (chave 'classificatoria' para a fase classificatória)
     */
    public static function mapaFases(Temporada $temporada, $corridasPrincipais)
    {
        if (!$temporada->temPlayoff()) {
            return [];
        }

        $qtdClassificatorias = $temporada->playoffQtdClassificatorias();
        $porFase = Temporada::PLAYOFF_CORRIDAS_POR_FASE;
        $fases = self::fasesDa($temporada);
        $mapa = [];

        foreach ($corridasPrincipais->values() as $i => $corrida) {
            if ($i < $qtdClassificatorias) {
                $mapa[$corrida->id] = ['chave' => 'classificatoria', 'nome' => 'Classificatória', 'sigla' => 'CL', 'n' => $i + 1, 'total' => $qtdClassificatorias];
                continue;
            }
            $idxFase = intdiv($i - $qtdClassificatorias, $porFase);
            if (!isset($fases[$idxFase])) {
                $mapa[$corrida->id] = ['chave' => 'fora', 'nome' => 'Após o playoff', 'sigla' => '-', 'n' => null, 'total' => null];
                continue;
            }
            $chave = $fases[$idxFase];
            $mapa[$corrida->id] = [
                'chave' => $chave,
                'nome' => self::FASES[$chave]['nome'],
                'sigla' => self::FASES[$chave]['sigla'],
                'n' => ($i - $qtdClassificatorias) % $porFase + 1,
                'total' => $porFase,
            ];
        }

        return $mapa;
    }

    /**
     * @param  array     $classificacaoAtual  resultadoPilotos de montaClassificacao(..., $ateOrdem, 'principal')
     * @param  callable  $classificacaoAte    fn(int $ordem): array  => resultadoPilotos até a ordem (corte das classificatórias)
     * @return array|null  null quando a temporada não tem playoff
     */
    public function montar($usuario, Temporada $temporada, $ateOrdem, array $classificacaoAtual, callable $classificacaoAte)
    {
        if (!$temporada->temPlayoff()) {
            return null;
        }

        $porFase = Temporada::PLAYOFF_CORRIDAS_POR_FASE;
        $classificados = $temporada->playoffQtdClassificados();
        $qtdClassificatorias = $temporada->playoffQtdClassificatorias();
        $ateOrdem = (int) $ateOrdem;

        $corridas = self::corridasPrincipais($usuario, $temporada->id);

        $comResultado = DB::table('resultados')
            ->whereIn('corrida_id', $corridas->pluck('id'))
            ->where('user_id', $usuario)
            ->whereNotNull('chegada')
            ->distinct()
            ->pluck('corrida_id')
            ->flip();

        $feita = fn ($c) => $c && isset($comResultado[$c->id]) && (int) $c->ordem <= $ateOrdem;

        // fase classificatória
        $classificatorias = $corridas->slice(0, $qtdClassificatorias)->values();
        $classificatoriasFeitas = $classificatorias->filter($feita)->count();
        $iniciado = $classificatoriasFeitas >= $qtdClassificatorias;

        // cabeças de chave: classificação no fim das classificatórias (ou provisória, enquanto não terminam)
        $linhas = $iniciado
            ? $classificacaoAte((int) $classificatorias->last()->ordem)
            : $classificacaoAtual;

        $seeds = [];
        foreach (array_slice($linhas, 0, $classificados) as $i => $linha) {
            $seeds[$i + 1] = [
                'seed' => $i + 1,
                'piloto_id' => (int) $linha->piloto_id,
                'pilotoEquipe_id' => (int) ($linha->pilotoEquipe_ultima_id ?? $linha->pilotoEquipe_id),
                'nome' => $linha->nome,
                'sobrenome' => $linha->sobrenome,
                'foto' => $linha->piloto_imagem,
                'logoEquipe' => $linha->equipe_ultima_imagem ?? $linha->equipe_imagem,
                'equipe' => $linha->equipe_ultima ?? $linha->equipe,
                'cor' => $linha->equipe_ultima_cor ?? $linha->cor,
                'pontos_classificacao' => (float) $linha->total,
            ];
        }

        // rodadas do mata-mata
        $rodadas = [];
        $offset = $qtdClassificatorias;
        $anterior = null;
        foreach (self::fasesDa($temporada) as $chave) {
            $lista = [];
            for ($i = 0; $i < $porFase; $i++) {
                $lista[] = $corridas[$offset + $i] ?? null;
            }
            $offset += $porFase;

            $feitas = array_values(array_filter($lista, $feita));
            $fase = [
                'feitas' => count($feitas),
                'total' => $porFase,
                'pontos' => $this->pontosPorPiloto($usuario, array_map(fn ($c) => $c->id, $feitas)),
            ];
            $sigla = self::FASES[$chave]['sigla'];

            $confrontos = [];
            if ($anterior === null) {
                foreach (self::PRIMEIRA_RODADA[$classificados] as $n => [$a, $b]) {
                    $c = $this->confronto($seeds[$a] ?? null, $seeds[$b] ?? null, $fase, $iniciado);
                    $c['rotulo'] = $sigla . ($n + 1) . ' · ' . $a . 'º x ' . $b . 'º';
                    $confrontos[] = $c;
                }
            } else {
                $siglaAnterior = self::FASES[$anterior['chave']]['sigla'];
                foreach (array_chunk($anterior['confrontos'], 2) as $n => [$x, $y]) {
                    $c = $this->confronto($x['vencedor'], $y['vencedor'], $fase, $iniciado);
                    $c['rotulo'] = ($chave === 'final' ? 'Final' : $sigla . ($n + 1))
                        . ' · venc. ' . $siglaAnterior . ($n * 2 + 1) . ' x ' . $siglaAnterior . ($n * 2 + 2);
                    $confrontos[] = $c;
                }
            }

            $rodadas[] = $anterior = [
                'chave' => $chave,
                'nome' => self::FASES[$chave]['nome'],
                'sigla' => $sigla,
                'corridas' => array_map(fn ($c) => $c ? [
                    'ordem' => (int) $c->ordem,
                    'evento' => optional($c->evento)->des_nome ?? optional($c->pista)->nome,
                    'feita' => $feita($c),
                ] : null, $lista),
                'feitas' => $fase['feitas'],
                'total' => $porFase,
                'confrontos' => $confrontos,
            ];
        }

        $final = end($rodadas)['confrontos'][0];
        $campeao = $final['vencedor'];
        $vice = $campeao ? ($final['a'] && $final['a']['piloto_id'] === $campeao['piloto_id'] ? $final['b'] : $final['a']) : null;

        // rodada atual = primeira com confronto ainda sem vencedor
        $rodadaAtual = null;
        foreach ($rodadas as $r) {
            if (!$this->todosDefinidos($r['confrontos'])) {
                $rodadaAtual = $r;
                break;
            }
        }

        if (!$iniciado) {
            $faseAtual = 'Classificatória (' . $classificatoriasFeitas . ' de ' . $qtdClassificatorias . ')';
        } elseif ($campeao) {
            $faseAtual = 'Encerrado';
        } else {
            $faseAtual = $rodadaAtual['nome'] . ' (' . $rodadaAtual['feitas'] . ' de ' . $porFase . ')';
        }

        // situação de cada piloto classificado (selo na tabela de classificação)
        $situacao = [];
        foreach ($seeds as $s) {
            $situacao[$s['piloto_id']] = ['seed' => $s['seed'], 'status' => $iniciado ? 'vivo' : 'provisorio', 'rotulo' => null, 'detalhe' => null];
        }
        if ($iniciado) {
            foreach ($rodadas as $r) {
                foreach ($r['confrontos'] as $c) {
                    foreach ([[$c['a'], $c['b'], $c['pontos_a'], $c['pontos_b']], [$c['b'], $c['a'], $c['pontos_b'], $c['pontos_a']]] as [$p, $op, $pp, $po]) {
                        if (!$p) {
                            continue;
                        }
                        $placar = $pp !== null ? ' · ' . $this->num($pp) . ' x ' . $this->num($po) : '';
                        $situacao[$p['piloto_id']]['rotulo'] = $r['sigla'];
                        $situacao[$p['piloto_id']]['detalhe'] = $r['nome'] . ' vs ' . ($op ? $op['nome'] . ' ' . $op['sobrenome'] : 'a definir') . $placar
                            . ' (' . $c['feitas'] . ' de ' . $c['total'] . ' corridas)';
                        if ($c['vencedor'] && $p['piloto_id'] !== $c['vencedor']['piloto_id']) {
                            $situacao[$p['piloto_id']]['status'] = 'eliminado';
                            $situacao[$p['piloto_id']]['rotulo'] = $r['chave'] === 'final' ? 'Vice' : 'Elim. ' . $r['sigla'];
                        }
                    }
                }
            }
            if ($campeao) {
                $situacao[$campeao['piloto_id']]['status'] = 'campeao';
                $situacao[$campeao['piloto_id']]['rotulo'] = 'Campeão';
            }
        }

        return [
            'qtd_corridas' => (int) $temporada->qtd_corridas,
            'qtd_classificados' => $classificados,
            'qtd_mata_mata' => $temporada->playoffCorridasMataMata(),
            'qtd_classificatorias' => $qtdClassificatorias,
            'classificatorias_feitas' => $classificatoriasFeitas,
            'iniciado' => $iniciado,
            'fase_atual' => $faseAtual,
            'rodada_atual' => $iniciado && !$campeao ? $rodadaAtual['chave'] : null,
            'corte' => $classificados,
            'seeds' => array_values($seeds),
            'rodadas' => $rodadas,
            'final' => $final,
            'campeao' => $campeao,
            'vice' => $vice,
            'situacao' => (object) $situacao,
        ];
    }

    /** [piloto_id => pontos] somando todos os vínculos do piloto (troca de equipe) nas corridas informadas */
    private function pontosPorPiloto($usuario, array $corridaIds)
    {
        if (!$corridaIds) {
            return [];
        }

        return DB::table('resultados')
            ->join('piloto_equipes', 'piloto_equipes.id', '=', 'resultados.pilotoEquipe_id')
            ->whereIn('resultados.corrida_id', $corridaIds)
            ->where('resultados.user_id', $usuario)
            ->groupBy('piloto_equipes.piloto_id')
            ->selectRaw('piloto_equipes.piloto_id, SUM(resultados.pontuacao) AS pontos')
            ->pluck('pontos', 'piloto_id')
            ->map(fn ($p) => (float) $p)
            ->all();
    }

    private function confronto($a, $b, array $fase, $iniciado)
    {
        $pontosA = (float) ($a ? ($fase['pontos'][$a['piloto_id']] ?? 0) : 0);
        $pontosB = (float) ($b ? ($fase['pontos'][$b['piloto_id']] ?? 0) : 0);
        $encerrado = $iniciado && $fase['feitas'] >= $fase['total'] && ($a || $b);

        $vencedor = null;
        if ($encerrado) {
            if (!$b) {
                $vencedor = $a;
            } elseif (!$a) {
                $vencedor = $b;
            } elseif ($pontosA !== $pontosB) {
                $vencedor = $pontosA > $pontosB ? $a : $b;
            } else {
                $vencedor = $a['seed'] < $b['seed'] ? $a : $b; // empate: melhor cabeça de chave
            }
        }

        // placar só aparece quando o confronto está definido e a fase já começou
        $emDisputa = $iniciado && $a && $b && $fase['feitas'] > 0;

        return [
            'a' => $a,
            'b' => $b,
            'pontos_a' => $emDisputa || $encerrado ? $pontosA : null,
            'pontos_b' => $emDisputa || $encerrado ? $pontosB : null,
            'feitas' => $iniciado && $a && $b ? $fase['feitas'] : 0,
            'total' => $fase['total'],
            'encerrado' => $encerrado,
            'vencedor' => $vencedor,
        ];
    }

    private function todosDefinidos(array $confrontos)
    {
        foreach ($confrontos as $c) {
            if (!$c['vencedor']) {
                return false;
            }
        }
        return true;
    }

    private function num($v)
    {
        return floor($v) == $v ? (string) (int) $v : str_replace('.', ',', (string) $v);
    }
}
