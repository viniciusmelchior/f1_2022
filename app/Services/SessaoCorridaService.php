<?php

namespace App\Services;

use App\Models\Site\Corrida;
use App\Models\Site\CorridaDetalhe;
use App\Models\Site\CorridaSessao;
use App\Models\Site\PilotoEquipe;
use App\Models\Site\ResultadoDetalhe;
use App\Models\Site\ResultadoVolta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Melhoria 2026-11-03
 *
 * Grava os JSONs das sessões do Content Manager (largada, chegada, grid invertido)
 * e os dados extraídos deles (resumo da corrida, dados por piloto e volta a volta),
 * atrelados a piloto_equipes / pilotos / equipes.
 *
 * NÃO altera largada, chegada, abandono nem pontuação (tabela "resultados"):
 * isso continua sendo feito exclusivamente pelo ResultadoController::update().
 *
 * A análise da corrida espelha a função JS analisarCorrida() da tela de resultados,
 * para que os números gravados sejam os mesmos exibidos.
 */
class SessaoCorridaService
{
    /**
     * Grava/remove as sessões enviadas pelo formulário e reconstrói os detalhes.
     * Campos do request:
     *   sessao_json[tipo]  => conteúdo do JSON (vazio = mantém o que já está salvo)
     *   sessao_nome[tipo]  => nome do arquivo
     *   sessao_remover[]   => tipos a remover
     */
    public function salvarDoRequest(Request $request, Corrida $corrida, int $userId): void
    {
        $jsons = (array) $request->input('sessao_json', []);
        $nomes = (array) $request->input('sessao_nome', []);
        $remover = (array) $request->input('sessao_remover', []);
        $alterou = false;

        DB::transaction(function () use ($jsons, $nomes, $remover, $corrida, $userId, &$alterou) {
            foreach ($remover as $tipo) {
                if (in_array($tipo, CorridaSessao::TIPOS, true) && empty($jsons[$tipo])) {
                    $alterou = CorridaSessao::where('corrida_id', $corrida->id)->where('user_id', $userId)->where('tipo', $tipo)->delete() > 0 || $alterou;
                }
            }

            foreach (CorridaSessao::TIPOS as $tipo) {
                $conteudo = trim((string) ($jsons[$tipo] ?? ''));
                if ($conteudo === '') {
                    continue;
                }
                $dados = json_decode($conteudo, true);
                if (!is_array($dados) || !isset($dados['sessions']) || !isset($dados['players'])) {
                    continue; // arquivo inválido: ignora sem afetar o restante
                }
                $hash = sha1($conteudo);
                $sessao = CorridaSessao::firstOrNew(['corrida_id' => $corrida->id, 'user_id' => $userId, 'tipo' => $tipo]);
                if ($sessao->exists && $sessao->hash === $hash) {
                    continue;
                }
                $sessao->fill(['nome_arquivo' => mb_substr((string) ($nomes[$tipo] ?? ''), 0, 255) ?: null, 'hash' => $hash, 'conteudo' => $conteudo]);
                $sessao->save();
                $alterou = true;
            }

            $possuiDetalhes = CorridaDetalhe::where('corrida_id', $corrida->id)->where('user_id', $userId)->exists();
            $possuiSessoes = CorridaSessao::where('corrida_id', $corrida->id)->where('user_id', $userId)->exists();
            if ($alterou || ($possuiSessoes && !$possuiDetalhes)) {
                $this->reconstruirDetalhes($corrida, $userId);
            }
        });
    }

    /**
     * Apaga e recria corrida_detalhes / resultado_detalhes / resultado_voltas
     * a partir das sessões salvas. Pode ser chamado a qualquer momento (reprocessamento).
     */
    public function reconstruirDetalhes(Corrida $corrida, int $userId): void
    {
        DB::transaction(function () use ($corrida, $userId) {
            ResultadoVolta::where('corrida_id', $corrida->id)->where('user_id', $userId)->delete();
            ResultadoDetalhe::where('corrida_id', $corrida->id)->where('user_id', $userId)->delete();
            CorridaDetalhe::where('corrida_id', $corrida->id)->where('user_id', $userId)->delete();

            $sessoes = CorridaSessao::where('corrida_id', $corrida->id)->where('user_id', $userId)->get()->keyBy('tipo');
            if ($sessoes->isEmpty()) {
                return;
            }

            $mapa = $this->mapaPilotos($corrida, $userId);

            // classificação: JSON de largada; na falta dele, o do grid invertido
            // classificação e treino: JSON de largada > grid invertido > o próprio JSON de chegada (fim de semana completo)
            $qualy = null;
            $treino = null;
            foreach (['largada', 'grid_invertido', 'chegada'] as $tipoFonte) {
                if (!$sessoes->has($tipoFonte)) {
                    continue;
                }
                $jsonFonte = json_decode($sessoes[$tipoFonte]->conteudo, true) ?: [];
                if (!$qualy) {
                    $analise = $this->analisarClassificacao($jsonFonte);
                    $qualy = ($analise && $analise['classificacao']) ? $analise : null;
                }
                if (!$treino) {
                    $treino = $this->analisarTreino($jsonFonte);
                }
            }
            $corridaAnalise = $sessoes->has('chegada') ? $this->analisarCorrida(json_decode($sessoes['chegada']->conteudo, true) ?: []) : null;

            // ---------- corrida_detalhes ----------
            $detalheCorrida = ['corrida_id' => $corrida->id, 'user_id' => $userId];
            $base = $corridaAnalise ?: $qualy; // condições/sessão vêm da corrida; sem ela, da classificação
            if ($base) {
                $detalheCorrida += $base['info'];
            }
            if ($corridaAnalise) {
                $vmr = $corridaAnalise['voltaMaisRapida'];
                $vencedor = $corridaAnalise['classificacao'][0] ?? null;
                $detalheCorrida += [
                    'total_voltas' => $corridaAnalise['totalVoltas'],
                    'total_carros' => count($corridaAnalise['classificacao']),
                    'total_abandonos' => count(array_filter($corridaAnalise['classificacao'], fn ($c) => $c['abandonou'])),
                    'total_paradas' => array_sum(array_map(fn ($c) => count($c['paradas']), $corridaAnalise['classificacao'])),
                    'tempo_corrida_ms' => $corridaAnalise['tempoCorrida'],
                    'media_volta_vencedor_ms' => $vencedor && $vencedor['voltasCompletadas'] ? (int) round($corridaAnalise['tempoCorrida'] / $vencedor['voltasCompletadas']) : null,
                    'vencedor_piloto_equipe_id' => $vencedor ? ($mapa[$vencedor['nomeNormalizado']]->id ?? null) : null,
                    'vencedor_nome' => $vencedor['nome'] ?? null,
                    'volta_mais_rapida_ms' => $vmr['melhorVolta'] ?? null,
                    'volta_mais_rapida_numero' => $vmr['numeroMelhorVolta'] ?? null,
                    'volta_mais_rapida_piloto_equipe_id' => $vmr ? ($mapa[$vmr['nomeNormalizado']]->id ?? null) : null,
                    'volta_mais_rapida_nome' => $vmr['nome'] ?? null,
                    'volta_ideal_ms' => $corridaAnalise['voltaIdeal'],
                    'voltas_neutralizadas' => $corridaAnalise['voltasNeutralizadas'] ? implode(',', $corridaAnalise['voltasNeutralizadas']) : null,
                ];
            }
            if ($qualy && $qualy['classificacao']) {
                $pole = $qualy['classificacao'][0];
                $detalheCorrida += [
                    'pole_tempo_ms' => $pole['tempo'],
                    'pole_piloto_equipe_id' => $mapa[$pole['nomeNormalizado']]->id ?? null,
                    'pole_nome' => $pole['nome'],
                    'classificacao_volta_ideal_ms' => $qualy['voltaIdealSessao'],
                ];
            }
            if ($treino && $treino['melhor']) {
                $detalheCorrida += [
                    'treino_melhor_volta_ms' => $treino['melhor']['melhor'],
                    'treino_melhor_piloto_equipe_id' => $mapa[$treino['melhor']['nomeNormalizado']]->id ?? null,
                    'treino_melhor_nome' => $treino['melhor']['nome'],
                ];
            }
            CorridaDetalhe::create($detalheCorrida);

            // ---------- resultado_detalhes / resultado_voltas ----------
            $pilotos = []; // nomeNormalizado => dados
            if ($treino) {
                foreach ($treino['pilotos'] as $t) {
                    $pilotos[$t['nomeNormalizado']] = ['nome' => $t['nome'], 'carro' => $t['carro'], 'config' => $t['config'], 'treino' => $t, 'qualy' => null, 'corrida' => null];
                }
            }
            if ($qualy) {
                foreach ($qualy['pilotos'] as $p) {
                    $pilotos[$p['nomeNormalizado']] = array_merge($pilotos[$p['nomeNormalizado']] ?? ['treino' => null, 'corrida' => null], [
                        'nome' => $p['nome'], 'carro' => $p['carro'], 'config' => $p['config'], 'qualy' => $p,
                    ]);
                }
            }
            if ($corridaAnalise) {
                foreach ($corridaAnalise['classificacao'] as $c) {
                    $pilotos[$c['nomeNormalizado']] = array_merge($pilotos[$c['nomeNormalizado']] ?? ['qualy' => null, 'treino' => null], [
                        'nome' => $c['nome'], 'carro' => $c['carro'], 'config' => $c['config'], 'corrida' => $c,
                    ]);
                }
            }

            foreach ($pilotos as $nomeNormalizado => $p) {
                $pe = $mapa[$nomeNormalizado] ?? null;
                $t = $p['treino'];
                $q = $p['qualy'];
                $c = $p['corrida'];
                $vincular = [
                    'corrida_id' => $corrida->id,
                    'user_id' => $userId,
                    'piloto_equipe_id' => $pe->id ?? null,
                    'piloto_id' => $pe->piloto_id ?? null,
                    'equipe_id' => $pe->equipe_id ?? null,
                ];

                $detalhe = ResultadoDetalhe::create($vincular + [
                    'nome_arquivo' => $p['nome'],
                    'carro' => $p['carro'],
                    'nacionalidade' => $p['config']['nacionalidade'],
                    'eh_jogador' => (bool) (($c['ehJogador'] ?? false) || ($q['ehJogador'] ?? false)),
                    'lastro' => $p['config']['lastro'],
                    'restritor' => $p['config']['restritor'],
                    'agressividade' => $p['config']['agressividade'],
                    'nivel_ia' => $p['config']['nivelIA'],

                    'posicao_classificacao' => $q['posicao'] ?? null,
                    'tempo_classificacao_ms' => $q['tempo'] ?? null,
                    'dif_pole_ms' => $q['difPole'] ?? null,
                    'voltas_classificacao' => $q['voltas'] ?? null,
                    'voltas_validas_classificacao' => $q['voltasValidas'] ?? null,
                    'volta_ideal_classificacao_ms' => $q['voltaIdeal'] ?? null,
                    'treino_posicao' => $t['posicao'] ?? null,
                    'treino_melhor_volta_ms' => $t['melhor'] ?? null,
                    'treino_voltas' => $t['voltas'] ?? null,
                    'treino_voltas_validas' => $t['voltasValidas'] ?? null,

                    'posicao' => $c['posicao'] ?? null,
                    'voltas_completadas' => $c['voltasCompletadas'] ?? null,
                    'tempo_total_ms' => $c['tempoTotal'] ?? null,
                    'dif_lider_ms' => $c['difLider'] ?? null,
                    'intervalo_ms' => $c['intervalo'] ?? null,
                    'voltas_atras' => $c['voltasAtras'] ?? null,
                    'abandonou' => $c ? $c['abandonou'] : null,
                    'volta_abandono' => $c['voltaAbandono'] ?? null,
                    'melhor_volta_ms' => $c['melhorVolta'] ?? null,
                    'melhor_volta_numero' => $c['numeroMelhorVolta'] ?? null,
                    'volta_mais_rapida' => $c && $corridaAnalise['voltaMaisRapida'] && $corridaAnalise['voltaMaisRapida']['indice'] === $c['indice'],
                    'melhor_s1_ms' => $c['melhoresSetores'][0] ?? null,
                    'melhor_s2_ms' => $c['melhoresSetores'][1] ?? null,
                    'melhor_s3_ms' => $c['melhoresSetores'][2] ?? null,
                    'ritmo_medio_ms' => isset($c['ritmoMedio']) ? (int) round($c['ritmoMedio']) : null,
                    'consistencia_ms' => isset($c['consistencia']) ? (int) round($c['consistencia']) : null,
                    'voltas_limpas' => $c['voltasLimpas'] ?? null,
                    'paradas' => $c ? count($c['paradas']) : null,
                    'voltas_paradas' => $c && $c['paradas'] ? implode(',', array_column($c['paradas'], 'volta')) : null,
                    'pneus' => $c ? implode(',', $c['pneus']) : null,
                    'cortes' => $c['cortes'] ?? null,
                    'posicao_volta1' => $c['posicaoPorVolta'][0] ?? null,
                ]);

                $linhas = [];
                if ($t) {
                    foreach ($t['listaVoltas'] as $v) {
                        $linhas[] = $vincular + $this->linhaVolta($detalhe->id, 'treino', $v, null, false, false);
                    }
                }
                if ($q) {
                    foreach ($q['listaVoltas'] as $v) {
                        $linhas[] = $vincular + $this->linhaVolta($detalhe->id, 'classificacao', $v, null, false, false);
                    }
                }
                if ($c) {
                    $voltasBox = array_column($c['paradas'], 'volta');
                    foreach ($c['voltas'] as $i => $v) {
                        $linhas[] = $vincular + $this->linhaVolta($detalhe->id, 'corrida', $v, $c['posicaoPorVolta'][$i] ?? null,
                            in_array($v['numero'], $voltasBox, true), in_array($v['numero'], $corridaAnalise['voltasNeutralizadas'], true));
                    }
                }
                foreach (array_chunk($linhas, 200) as $lote) {
                    ResultadoVolta::insert($lote);
                }
            }
        });
    }

    private function linhaVolta(int $detalheId, string $sessao, array $v, ?int $posicao, bool $box, bool $neutralizada): array
    {
        return [
            'resultado_detalhe_id' => $detalheId,
            'sessao' => $sessao,
            'volta' => $v['numero'],
            'tempo_ms' => $v['tempo'],
            'valida' => $v['valida'],
            's1_ms' => $v['setores'][0] ?? null,
            's2_ms' => $v['setores'][1] ?? null,
            's3_ms' => $v['setores'][2] ?? null,
            'pneu' => $v['pneu'],
            'cortes' => $v['cortes'],
            'posicao' => $posicao,
            'entrada_box' => $box,
            'neutralizada' => $neutralizada,
        ];
    }

    /**
     * nome normalizado "NOME SOBRENOME" => PilotoEquipe, com o mesmo grid usado na tela de edição.
     * Em nomes repetidos, prioriza o registro de super corrida apenas quando a corrida é super corrida.
     */
    public function mapaPilotos(Corrida $corrida, int $userId): array
    {
        $grid = PilotoEquipe::with('piloto')
            ->where('user_id', $userId)
            ->where('ano_id', $corrida->temporada->ano_id)
            ->where('flg_ativo', 'S')
            ->orderBy('equipe_id')
            ->get();

        $superCorrida = $corrida->flg_super_corrida == 'S';
        $mapa = [];
        foreach ($grid as $pe) {
            if (!$pe->piloto) {
                continue;
            }
            $nome = self::normalizarNome($pe->piloto->nome . ' ' . $pe->piloto->sobrenome);
            $ehSuper = $pe->flg_super_corrida == 'S';
            if (!isset($mapa[$nome]) || ($ehSuper === $superCorrida && ($mapa[$nome]->flg_super_corrida == 'S') !== $superCorrida)) {
                $mapa[$nome] = $pe;
            }
        }
        return $mapa;
    }

    /* ============================ análise ============================ */

    public static function normalizarNome(?string $nome): string
    {
        $mapa = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n', 'ý' => 'y', 'ÿ' => 'y', 'ć' => 'c', 'č' => 'c',
            'š' => 's', 'ž' => 'z', 'ł' => 'l', 'ń' => 'n', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'ř' => 'r', 'ě' => 'e', 'ő' => 'o', 'ű' => 'u',
        ];
        $nome = mb_strtolower((string) $nome, 'UTF-8');
        $nome = strtr($nome, $mapa);
        $nome = preg_replace('/\s+/u', ' ', $nome);
        return mb_strtoupper(trim($nome), 'UTF-8');
    }

    private static function numeroOuNulo($valor): ?float
    {
        return ($valor === null || $valor === '' || !is_numeric($valor)) ? null : (float) $valor;
    }

    private static function mediana(array $valores): ?float
    {
        $v = array_values(array_filter($valores, fn ($x) => $x > 0));
        sort($v);
        $n = count($v);
        if (!$n) {
            return null;
        }
        $m = intdiv($n, 2);
        return $n % 2 ? $v[$m] : ($v[$m - 1] + $v[$m]) / 2;
    }

    private static function desvioPadrao(array $valores): ?float
    {
        $n = count($valores);
        if ($n < 2) {
            return null;
        }
        $media = array_sum($valores) / $n;
        $soma = 0;
        foreach ($valores as $v) {
            $soma += ($v - $media) ** 2;
        }
        return sqrt($soma / ($n - 1));
    }

    public static function lerIni(?string $texto): array
    {
        $ini = [];
        $secao = null;
        foreach (preg_split('/\r?\n/', (string) $texto) as $linha) {
            $linha = trim($linha);
            if ($linha === '' || $linha[0] === ';') {
                continue;
            }
            if (preg_match('/^\[(.+)\]$/', $linha, $m)) {
                $secao = $m[1];
                $ini[$secao] = $ini[$secao] ?? [];
                continue;
            }
            $pos = strpos($linha, '=');
            if ($secao !== null && $pos !== false) {
                $ini[$secao][trim(substr($linha, 0, $pos))] = trim(substr($linha, $pos + 1));
            }
        }
        return $ini;
    }

    private static function descreverClima(string $codigo): string
    {
        $nome = strtolower($codigo);
        $mapa = [
            'thunder' => 'Tempestade', 'storm' => 'Tempestade', 'heavy_rain' => 'Chuva forte', 'light_rain' => 'Chuva fraca',
            'drizzle' => 'Garoa', 'rain' => 'Chuva', 'snow' => 'Neve', 'sleet' => 'Granizo', 'heavy_fog' => 'Neblina forte',
            'fog' => 'Neblina', 'mist' => 'Névoa', 'haze' => 'Névoa seca', 'overcast' => 'Encoberto', 'heavy_clouds' => 'Muito nublado',
            'mid_clouds' => 'Parcialmente nublado', 'light_clouds' => 'Poucas nuvens', 'few_clouds' => 'Poucas nuvens', 'cloud' => 'Nublado',
            'windy' => 'Ventania', 'clear' => 'Céu limpo', 'sunny' => 'Ensolarado',
        ];
        foreach ($mapa as $chave => $descricao) {
            if (strpos($nome, $chave) !== false) {
                return $descricao;
            }
        }
        return $codigo !== '' ? $codigo : 'Não informado';
    }

    /** Configuração de cada carro (CAR_n do race.ini) e informações gerais da sessão. */
    private function lerContexto(array $json): array
    {
        $ini = self::lerIni($json['__raceIni'] ?? '');
        $quickDrive = json_decode($json['__quickDrive'] ?? '', true) ?: [];
        $assist = json_decode($quickDrive['AssistsData'] ?? '', true) ?: [];
        $temperatura = $ini['TEMPERATURE'] ?? [];
        $vento = $ini['WIND'] ?? [];
        $corrida = $ini['RACE'] ?? [];
        $dinamica = $ini['DYNAMIC_TRACK'] ?? [];
        $clima = $ini['WEATHER']['NAME'] ?? '';

        $agressividades = [];
        foreach ($ini as $secao => $valores) {
            if (preg_match('/^CAR_\d+$/', $secao) && isset($valores['AI_AGGRESSION']) && is_numeric($valores['AI_AGGRESSION'])) {
                $agressividades[] = (float) $valores['AI_AGGRESSION'];
            }
        }
        $largadaJogador = null;
        foreach ($ini as $secao => $valores) {
            if (preg_match('/^SESSION_\d+$/', $secao) && ($valores['TYPE'] ?? null) == '3' && isset($valores['STARTING_POSITION'])) {
                $largadaJogador = (int) $valores['STARTING_POSITION'];
            }
        }

        $horario = null;
        if (isset($quickDrive['Time']) && is_numeric($quickDrive['Time'])) {
            $seg = (int) $quickDrive['Time'];
            $horario = sprintf('%02d:%02d', intdiv($seg, 3600), intdiv($seg % 3600, 60));
        }
        $temperaturaAr = self::numeroOuNulo($temperatura['AMBIENT'] ?? null);
        if ($temperaturaAr === null && isset($quickDrive['Temperature'])) {
            $temperaturaAr = round((float) $quickDrive['Temperature']);
        }
        $data = isset($quickDrive['dtv']) ? substr((string) $quickDrive['dtv'], 0, 10) : null;

        return [
            'ini' => $ini,
            'info' => [
                'pista_codigo' => $corrida['TRACK'] ?? ($json['track'] ?? null),
                'pista_layout' => $corrida['CONFIG_TRACK'] ?? null,
                'data_sessao' => ($data && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) ? $data : null,
                'horario' => $horario,
                'clima_codigo' => $clima ?: null,
                'clima_descricao' => self::descreverClima($clima),
                'pista_molhada' => (bool) preg_match('/rain|drizzle|storm|thunder|snow|sleet/i', $clima),
                'temperatura_ar' => $temperaturaAr,
                'temperatura_pista' => self::numeroOuNulo($temperatura['ROAD'] ?? null),
                'vento_min_kmh' => self::numeroOuNulo($vento['SPEED_KMH_MIN'] ?? null),
                'vento_max_kmh' => self::numeroOuNulo($vento['SPEED_KMH_MAX'] ?? null),
                'vento_direcao' => self::numeroOuNulo($vento['DIRECTION_DEG'] ?? null),
                'aderencia_inicial' => self::numeroOuNulo($dinamica['SESSION_START'] ?? null),
                'ganho_aderencia' => self::numeroOuNulo($dinamica['LAP_GAIN'] ?? null),
                'nivel_ia' => self::numeroOuNulo($corrida['AI_LEVEL'] ?? null),
                'agressividade_media' => $agressividades ? round(array_sum($agressividades) / count($agressividades), 1) : null,
                'desgaste_pneus' => self::numeroOuNulo($assist['TyreWear'] ?? null),
                'consumo_combustivel' => self::numeroOuNulo($assist['FuelConsumption'] ?? null),
                'dano' => self::numeroOuNulo($assist['Damage'] ?? null),
                'penalidades' => isset($corrida['PENALTIES']) ? $corrida['PENALTIES'] == '1' : null,
                'setup_fixo' => isset($corrida['FIXED_SETUP']) ? $corrida['FIXED_SETUP'] == '1' : null,
                'largada_jogador' => $largadaJogador,
            ],
        ];
    }

    private function configCarro(array $ini, int $indice): array
    {
        $c = $ini['CAR_' . $indice] ?? [];
        return [
            'nacionalidade' => $c['NATION_CODE'] ?? null,
            'agressividade' => self::numeroOuNulo($c['AI_AGGRESSION'] ?? null),
            'nivelIA' => self::numeroOuNulo($c['AI_LEVEL'] ?? null),
            'lastro' => self::numeroOuNulo($c['BALLAST'] ?? null),
            'restritor' => self::numeroOuNulo($c['RESTRICTOR'] ?? null),
            'ehJogador' => $indice === 0 && !isset($c['AI_LEVEL']),
        ];
    }

    private function voltasDoCarro(array $sessao, int $indice): array
    {
        $voltas = array_values(array_filter($sessao['laps'] ?? [], fn ($v) => (int) ($v['car'] ?? -1) === $indice));
        usort($voltas, fn ($a, $b) => $a['lap'] <=> $b['lap']);
        return array_map(fn ($v, $i) => [
            'numero' => $i + 1,
            // voltas inválidas (corte / volta de saída) vêm com time = -1 no JSON
            'tempo' => ((int) ($v['time'] ?? 0)) > 0 ? (int) $v['time'] : null,
            'valida' => ((int) ($v['time'] ?? 0)) > 0 && ((int) ($v['cuts'] ?? 0)) === 0,
            'setores' => array_map('intval', $v['sectors'] ?? []),
            'cortes' => (int) ($v['cuts'] ?? 0),
            'pneu' => $v['tyre'] ?? null,
        ], $voltas, array_keys($voltas));
    }

    /** Soma dos melhores setores entre as voltas válidas (volta "ideal"/potencial). */
    private static function voltaIdeal(array $voltas): ?int
    {
        $melhores = [];
        foreach ($voltas as $v) {
            if (!$v['valida']) {
                continue;
            }
            foreach ($v['setores'] as $s => $tempo) {
                if ($tempo > 0 && (!isset($melhores[$s]) || $tempo < $melhores[$s])) {
                    $melhores[$s] = $tempo;
                }
            }
        }
        return count($melhores) >= 3 ? array_sum($melhores) : null;
    }

    /** Treino livre (Practice): posição pela melhor volta válida. */
    public function analisarTreino(array $json): ?array
    {
        $sessao = null;
        foreach ($json['sessions'] ?? [] as $s) {
            if ((($s['type'] ?? null) == 1 || stripos($s['name'] ?? '', 'practice') === 0) && !empty($s['laps'])) {
                $sessao = $s; // fica com o último treino do arquivo
            }
        }
        if (!$sessao) {
            return null;
        }
        $contexto = $this->lerContexto($json);
        $pilotos = [];
        foreach ($json['players'] ?? [] as $indice => $jogador) {
            $voltas = $this->voltasDoCarro($sessao, $indice);
            $validas = array_filter($voltas, fn ($v) => $v['valida']);
            $melhor = null;
            foreach ($sessao['bestLaps'] ?? [] as $b) {
                if ((int) $b['car'] === $indice && $b['time'] > 0) {
                    $melhor = (int) $b['time'];
                }
            }
            if ($melhor === null && $validas) {
                $melhor = min(array_column($validas, 'tempo'));
            }
            $nome = trim(preg_replace('/\s+/u', ' ', (string) ($jogador['name'] ?? '')));
            $pilotos[] = [
                'nome' => $nome,
                'nomeNormalizado' => self::normalizarNome($nome),
                'carro' => $jogador['car'] ?? null,
                'config' => $this->configCarro($contexto['ini'], $indice),
                'melhor' => $melhor,
                'voltas' => count($voltas),
                'voltasValidas' => count($validas),
                'listaVoltas' => $voltas,
                'posicao' => null,
            ];
        }
        $comTempo = array_filter($pilotos, fn ($p) => $p['melhor']);
        uasort($comTempo, fn ($a, $b) => $a['melhor'] <=> $b['melhor']);
        $posicao = 0;
        foreach (array_keys($comTempo) as $chave) {
            $pilotos[$chave]['posicao'] = ++$posicao;
        }
        $primeiro = $comTempo ? reset($comTempo) : null;
        return ['pilotos' => $pilotos, 'melhor' => $primeiro];
    }

    /** Classificação (Qualifying): posição pela melhor volta válida (bestLaps), igual à lógica de largada da tela. */
    public function analisarClassificacao(array $json): ?array
    {
        $sessao = null;
        foreach ($json['sessions'] ?? [] as $s) {
            if (($s['name'] ?? '') === 'Qualifying') {
                $sessao = $s;
            }
        }
        if (!$sessao) {
            foreach ($json['sessions'] ?? [] as $s) {
                if (($s['type'] ?? null) == 2 && !empty($s['bestLaps'])) {
                    $sessao = $s;
                }
            }
        }
        if (!$sessao) {
            return null;
        }
        $contexto = $this->lerContexto($json);
        $jogadores = $json['players'] ?? [];

        $melhores = array_values(array_filter($sessao['bestLaps'] ?? [], fn ($b) => ($b['time'] ?? 0) > 0));
        usort($melhores, fn ($a, $b) => $a['time'] <=> $b['time']);
        $posicoes = [];
        foreach ($melhores as $i => $b) {
            $posicoes[(int) $b['car']] = ['posicao' => $i + 1, 'tempo' => (int) $b['time']];
        }
        $tempoPole = $melhores[0]['time'] ?? null;

        $pilotos = [];
        foreach ($jogadores as $indice => $jogador) {
            $nome = trim(preg_replace('/\s+/u', ' ', (string) ($jogador['name'] ?? '')));
            $voltas = $this->voltasDoCarro($sessao, $indice);
            $config = $this->configCarro($contexto['ini'], $indice);
            $pos = $posicoes[$indice] ?? null;
            $pilotos[] = [
                'indice' => $indice,
                'nome' => $nome,
                'nomeNormalizado' => self::normalizarNome($nome),
                'carro' => $jogador['car'] ?? null,
                'config' => $config,
                'ehJogador' => $config['ehJogador'],
                'voltasValidas' => count(array_filter($voltas, fn ($v) => $v['valida'])),
                'voltaIdeal' => self::voltaIdeal($voltas),
                'posicao' => $pos['posicao'] ?? null,
                'tempo' => $pos['tempo'] ?? null,
                'difPole' => ($pos && $tempoPole !== null) ? $pos['tempo'] - (int) $tempoPole : null,
                'voltas' => isset($sessao['lapstotal'][$indice]) ? (int) $sessao['lapstotal'][$indice] : count($voltas),
                'listaVoltas' => $voltas,
            ];
        }
        $classificacao = array_values(array_filter($pilotos, fn ($p) => $p['posicao'] !== null));
        usort($classificacao, fn ($a, $b) => $a['posicao'] <=> $b['posicao']);

        // volta ideal da sessão: melhores setores de todos os pilotos (somente voltas válidas)
        $todas = [];
        foreach ($pilotos as $p) {
            $todas = array_merge($todas, $p['listaVoltas']);
        }

        return ['info' => $contexto['info'], 'pilotos' => $pilotos, 'classificacao' => $classificacao, 'voltaIdealSessao' => self::voltaIdeal($todas)];
    }

    /** Corrida: mesma regra da tela (voltas desc, tempo total asc; abandono = não completou e tempo < vencedor). */
    public function analisarCorrida(array $json): ?array
    {
        $sessao = null;
        foreach ($json['sessions'] ?? [] as $s) {
            if (($s['name'] ?? '') === 'Quick Race' && !empty($s['laps'])) {
                $sessao = $s;
                break;
            }
        }
        if (!$sessao) {
            foreach ($json['sessions'] ?? [] as $s) {
                if (($s['type'] ?? null) == 3 && !empty($s['laps'])) {
                    $sessao = $s;
                    break;
                }
            }
        }
        if (!$sessao) {
            return null;
        }
        $contexto = $this->lerContexto($json);
        $jogadores = $json['players'] ?? [];
        $laps = $sessao['laps'] ?? [];
        $lapstotal = $sessao['lapstotal'] ?? [];
        $totalVoltas = (int) ($sessao['lapsCount'] ?? 0) ?: ($lapstotal ? max($lapstotal) : 0);

        // voltas lentas do pelotão (mediana da volta > 1.5x a mediana geral)
        $maxVolta = -1;
        foreach ($laps as $v) {
            $maxVolta = max($maxVolta, (int) $v['lap']);
        }
        $medianas = [];
        for ($k = 0; $k <= $maxVolta; $k++) {
            $medianas[$k] = self::mediana(array_map(fn ($v) => (float) $v['time'], array_filter($laps, fn ($v) => (int) $v['lap'] === $k)));
        }
        $medianaGeral = self::mediana(array_filter($medianas, fn ($m, $k) => $k > 0 && $m, ARRAY_FILTER_USE_BOTH));
        $neutralizadas = [];
        foreach ($medianas as $k => $m) {
            if ($k > 0 && $m && $medianaGeral && $m > $medianaGeral * 1.5) {
                $neutralizadas[] = $k + 1;
            }
        }

        $carros = [];
        foreach ($jogadores as $indice => $jogador) {
            $voltas = $this->voltasDoCarro($sessao, $indice);
            $tempoTotal = array_sum(array_column($voltas, 'tempo'));
            $acumulado = [];
            $soma = 0;
            foreach ($voltas as $i => $v) {
                $soma += $v['tempo'];
                $acumulado[$i] = $soma;
            }
            $voltasCompletadas = isset($lapstotal[$indice]) ? (int) $lapstotal[$indice] : count($voltas);

            $melhor = null;
            foreach ($sessao['bestLaps'] ?? [] as $b) {
                if ((int) $b['car'] === $indice && $b['time'] > 0) {
                    $melhor = ['time' => (int) $b['time'], 'lap' => (int) $b['lap']];
                    break;
                }
            }
            if (!$melhor) {
                foreach ($voltas as $i => $v) {
                    if ($v['tempo'] > 0 && (!$melhor || $v['tempo'] < $melhor['time'])) {
                        $melhor = ['time' => $v['tempo'], 'lap' => $i];
                    }
                }
            }

            $melhoresSetores = [];
            foreach ($voltas as $v) {
                foreach ($v['setores'] as $s => $tempo) {
                    if ($tempo > 0 && (!isset($melhoresSetores[$s]) || $tempo < $melhoresSetores[$s])) {
                        $melhoresSetores[$s] = $tempo;
                    }
                }
            }

            $stints = [];
            foreach ($voltas as $i => $v) {
                $pneu = $v['pneu'] ?? '?';
                $ultimo = count($stints) - 1;
                if ($ultimo >= 0 && $stints[$ultimo]['pneu'] === $pneu) {
                    $stints[$ultimo]['voltas']++;
                } else {
                    $stints[] = ['pneu' => $pneu, 'inicio' => $i + 1, 'voltas' => 1];
                }
            }
            $paradas = [];
            for ($s = 1; $s < count($stints); $s++) {
                $paradas[] = ['volta' => $stints[$s]['inicio'] - 1, 'de' => $stints[$s - 1]['pneu'], 'para' => $stints[$s]['pneu']];
            }
            $voltasBox = [];
            foreach ($paradas as $p) {
                $voltasBox[] = $p['volta'];
                $voltasBox[] = $p['volta'] + 1;
            }

            $tempoMelhor = $melhor['time'] ?? null;
            $limpas = [];
            foreach ($voltas as $i => $v) {
                if ($i > 0 && $v['tempo'] && !in_array($i + 1, $voltasBox, true) && !in_array($i + 1, $neutralizadas, true) && $tempoMelhor && $v['tempo'] <= $tempoMelhor * 1.07) {
                    $limpas[] = $v['tempo'];
                }
            }

            $nome = trim(preg_replace('/\s+/u', ' ', (string) ($jogador['name'] ?? '')));
            $config = $this->configCarro($contexto['ini'], $indice);
            $carros[] = [
                'indice' => $indice,
                'nome' => $nome,
                'nomeNormalizado' => self::normalizarNome($nome),
                'carro' => $jogador['car'] ?? null,
                'config' => $config,
                'ehJogador' => $config['ehJogador'],
                'voltas' => $voltas,
                'acumulado' => $acumulado,
                'voltasCompletadas' => $voltasCompletadas,
                'tempoTotal' => $tempoTotal,
                'melhorVolta' => $tempoMelhor,
                'numeroMelhorVolta' => $melhor ? $melhor['lap'] + 1 : null,
                'melhoresSetores' => $melhoresSetores,
                'pneus' => array_column($stints, 'pneu'),
                'paradas' => $paradas,
                'ritmoMedio' => $limpas ? array_sum($limpas) / count($limpas) : null,
                'consistencia' => count($limpas) >= 5 ? self::desvioPadrao($limpas) : null,
                'voltasLimpas' => count($limpas),
                'cortes' => array_sum(array_column($voltas, 'cortes')),
            ];
        }

        usort($carros, function ($a, $b) {
            if ($b['voltasCompletadas'] !== $a['voltasCompletadas']) {
                return $b['voltasCompletadas'] <=> $a['voltasCompletadas'];
            }
            if ($a['tempoTotal'] !== $b['tempoTotal']) {
                return $a['tempoTotal'] <=> $b['tempoTotal'];
            }
            return $a['indice'] <=> $b['indice'];
        });

        $vencedor = $carros[0] ?? null;
        $tempoVencedor = $vencedor['tempoTotal'] ?? 0;
        foreach ($carros as $i => &$c) {
            $c['posicao'] = $i + 1;
            $terminou = $c['voltasCompletadas'] >= $totalVoltas || ($c['voltasCompletadas'] > 0 && $c['tempoTotal'] >= $tempoVencedor);
            $c['abandonou'] = !$terminou;
            $c['voltaAbandono'] = $terminou ? null : $c['voltasCompletadas'] + 1;
            $c['voltasAtras'] = $vencedor ? $vencedor['voltasCompletadas'] - $c['voltasCompletadas'] : 0;
            $c['difLider'] = ($i > 0 && $c['voltasAtras'] === 0) ? $c['tempoTotal'] - $tempoVencedor : null;
            $anterior = $carros[$i - 1] ?? null;
            $c['intervalo'] = ($anterior && $anterior['voltasCompletadas'] === $c['voltasCompletadas']) ? $c['tempoTotal'] - $anterior['tempoTotal'] : null;
        }
        unset($c);

        // posição ao final de cada volta
        $colunas = 0;
        foreach ($carros as $c) {
            $colunas = max($colunas, count($c['voltas']));
        }
        foreach ($carros as &$c) {
            $c['posicaoPorVolta'] = array_fill(0, $colunas, null);
        }
        unset($c);
        for ($k = 0; $k < $colunas; $k++) {
            $naVolta = array_keys(array_filter($carros, fn ($c) => isset($c['acumulado'][$k])));
            usort($naVolta, fn ($a, $b) => $carros[$a]['acumulado'][$k] <=> $carros[$b]['acumulado'][$k]);
            foreach ($naVolta as $p => $chave) {
                $carros[$chave]['posicaoPorVolta'][$k] = $p + 1;
            }
        }

        $vmr = null;
        foreach ($carros as $c) {
            if ($c['melhorVolta'] && (!$vmr || $c['melhorVolta'] < $vmr['melhorVolta'])) {
                $vmr = $c;
            }
        }
        $numeroSetores = 0;
        foreach ($carros as $c) {
            $numeroSetores = max($numeroSetores, count($c['melhoresSetores']));
        }
        $voltaIdeal = null;
        if ($numeroSetores) {
            $voltaIdeal = 0;
            for ($s = 0; $s < $numeroSetores; $s++) {
                $melhorSetor = null;
                foreach ($carros as $c) {
                    if (!empty($c['melhoresSetores'][$s]) && ($melhorSetor === null || $c['melhoresSetores'][$s] < $melhorSetor)) {
                        $melhorSetor = $c['melhoresSetores'][$s];
                    }
                }
                if ($melhorSetor === null) {
                    $voltaIdeal = null;
                    break;
                }
                $voltaIdeal += $melhorSetor;
            }
        }

        return [
            'info' => $contexto['info'] + ['sessao_nome' => $sessao['name'] ?? null],
            'totalVoltas' => $totalVoltas,
            'tempoCorrida' => $tempoVencedor,
            'classificacao' => $carros,
            'voltaMaisRapida' => $vmr,
            'voltaIdeal' => $voltaIdeal,
            'voltasNeutralizadas' => $neutralizadas,
        ];
    }
}
