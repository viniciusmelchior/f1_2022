<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Request values never reach a query as SQL text. Every parameter that used to be pasted into a
 * statement is sent a UNION payload that would read another account's data, then a normal value
 * that must give the same rows as before.
 */
class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = '1 UNION SELECT id, name, email, password, 1, 1, 1, 1, 1, 1, 1, 1, 1 FROM users';
    private const SECRET = 'Segredo da Outra Conta';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'x']);
        $other = User::create(['name' => self::SECRET, 'email' => 'outra@example.com', 'password' => 'x']);

        $uid = $this->user->id;

        DB::table('anos')->insert(['id' => 1, 'ano' => '2022', 'user_id' => $uid, 'flg_ativo' => 'S']);
        DB::table('paises')->insert(['id' => 1, 'des_nome' => 'Brasil', 'user_id' => $uid]);
        DB::table('pistas')->insert(['id' => 1, 'nome' => 'Interlagos', 'pais_id' => 1, 'user_id' => $uid, 'flg_ativo' => 'S', 'tamanho_km' => 4, 'qtd_voltas' => 71]);
        DB::table('eventos')->insert(['id' => 1, 'des_nome' => 'GP', 'user_id' => $uid]);

        foreach ([[1, 'Max', 'Verstappen'], [2, 'Sergio', 'Perez']] as [$id, $nome, $sobrenome]) {
            DB::table('pilotos')->insert(['id' => $id, 'nome' => $nome, 'sobrenome' => $sobrenome, 'pais_id' => 1, 'user_id' => $uid, 'flg_ativo' => 'S']);
        }
        DB::table('equipes')->insert(['id' => 1, 'nome' => 'Red Bull', 'des_cor' => '#3671C6', 'pais_id' => 1, 'user_id' => $uid, 'flg_ativo' => 'S']);
        foreach ([1, 2] as $id) {
            DB::table('piloto_equipes')->insert(['id' => $id, 'piloto_id' => $id, 'equipe_id' => 1, 'user_id' => $uid, 'ano_id' => 1, 'flg_ativo' => 'S']);
        }

        DB::table('temporadas')->insert([
            ['id' => 1, 'des_temporada' => 'Temporada 1', 'ano_id' => 1, 'user_id' => $uid, 'flg_finalizada' => 'N'],
            ['id' => 2, 'des_temporada' => 'Temporada 2', 'ano_id' => 1, 'user_id' => $uid, 'flg_finalizada' => 'N'],
            ['id' => 3, 'des_temporada' => 'Da outra conta', 'ano_id' => 1, 'user_id' => $other->id, 'flg_finalizada' => 'N'],
        ]);

        // Season 1: two races and a sprint (same order as race 2). Season 2: one race.
        foreach ([[1, 1, 1, 'N'], [2, 1, 2, 'N'], [3, 1, 2, 'S'], [4, 2, 1, 'N']] as [$id, $temporada, $ordem, $sprint]) {
            DB::table('corridas')->insert(['id' => $id, 'temporada_id' => $temporada, 'pista_id' => 1, 'ordem' => $ordem, 'flg_sprint' => $sprint, 'user_id' => $uid, 'evento_id' => 1, 'exibir_resultado' => 1]);
        }

        // corrida, pilotoEquipe, largada, chegada, pontos
        $resultados = [
            [1, 1, 1, 1, 25], [1, 2, 2, 2, 18],
            [2, 1, 2, 2, 18], [2, 2, 1, 1, 25],
            [3, 1, 1, 1, 8], [3, 2, 2, 2, 7],
            [4, 1, 1, 1, 25], [4, 2, 2, 2, 18],
        ];
        foreach ($resultados as $i => [$corrida, $pe, $largada, $chegada, $pontos]) {
            DB::table('resultados')->insert([
                'id' => $i + 1, 'corrida_id' => $corrida, 'pilotoEquipe_id' => $pe, 'largada' => $largada, 'chegada' => $chegada,
                'pontuacao' => $pontos, 'pontuacao_classica' => $pontos, 'pontuacao_personalizada' => $pontos, 'pontuacao_invertida' => $pontos,
                'flg_abandono' => 'N', 'user_id' => $uid,
            ]);
        }

        $this->actingAs($this->user);
    }

    private function assertRefused($response): void
    {
        $response->assertStatus(422);
        $this->assertStringNotContainsString(self::SECRET, $response->getContent());
        $this->assertStringNotContainsString('outra@example.com', $response->getContent());
    }

    /** @return array<string, array{string, array<string, mixed>, string}> uri, valid request, the parameter name to attack */
    public static function endpoints(): array
    {
        $comparativos = ['temporada_id' => 1, 'pilotosId' => [1, 2]];

        return [
            'classificacao: temporada_id' => ['/ajax/classificacaoGeralPorTemporada', ['temporada_id' => 1], 'temporada_id'],
            'comparativos: temporada_id' => ['/ajax/comparativos', $comparativos, 'temporada_id'],
            'comparativos: first piloto' => ['/ajax/comparativos', $comparativos, 'pilotosId.0'],
            'comparativos: second piloto' => ['/ajax/comparativos', $comparativos, 'pilotosId.1'],
            'classificacao da temporada: idCorrida' => ['/temporadas/classificacao', ['id' => 1, 'idCorrida' => 2], 'idCorrida'],
            'classificacao apos corrida: corrida_id' => ['/fetch/getClassificacaoAposCorrida', ['temporada_id' => 1, 'corrida_id' => 2], 'corrida_id'],
            'vitorias pilotos' => ['/ajax/ajaxGetVitoriasPilotoPorTemporada', ['vitoriasPilotosTemporadaId' => 1], 'vitoriasPilotosTemporadaId'],
            'vitorias equipes' => ['/ajax/ajaxGetVitoriasEquipePorTemporada', ['vitoriasEquipesTemporadaId' => 1], 'vitoriasEquipesTemporadaId'],
            'poles pilotos' => ['/ajax/ajaxGetPolesPilotosPorTemporada', ['polesPilotosTemporadaId' => 1], 'polesPilotosTemporadaId'],
            'poles equipes' => ['/ajax/ajaxGetPolesEquipesPorTemporada', ['polesEquipesTemporadaId' => 1], 'polesEquipesTemporadaId'],
            'podios pilotos' => ['/ajax/ajaxGetPodiosPilotoPorTemporada', ['podiosPilotosTemporadaId' => 1], 'podiosPilotosTemporadaId'],
            'podios equipes' => ['/ajax/ajaxGetPodiosEquipesPorTemporada', ['podiosEquipesTemporadaId' => 1], 'podiosEquipesTemporadaId'],
            'chegadas pilotos: temporada' => ['/ajax/ajaxGetChegadasPilotosPorTemporada', ['temporada' => 1, 'inicio' => 1, 'fim' => 3], 'temporada'],
            'chegadas pilotos: inicio' => ['/ajax/ajaxGetChegadasPilotosPorTemporada', ['temporada' => 1, 'inicio' => 1, 'fim' => 3], 'inicio'],
            'chegadas pilotos: fim' => ['/ajax/ajaxGetChegadasPilotosPorTemporada', ['temporada' => 1, 'inicio' => 1, 'fim' => 3], 'fim'],
            'chegadas equipes: temporada' => ['/ajax/ajaxGetChegadasEquipesPorTemporada', ['temporada' => 1, 'inicio' => 1, 'fim' => 3], 'temporada'],
            'chegadas equipes: inicio' => ['/ajax/ajaxGetChegadasEquipesPorTemporada', ['temporada' => 1, 'inicio' => 1, 'fim' => 3], 'inicio'],
            'chegadas equipes: fim' => ['/ajax/ajaxGetChegadasEquipesPorTemporada', ['temporada' => 1, 'inicio' => 1, 'fim' => 3], 'fim'],
            'estatisticas: limite' => ['/estatisticasPilotosEquipes/buscar', self::estatisticas(), 'limite'],
            'estatisticas: tipo' => ['/estatisticasPilotosEquipes/buscar', self::estatisticas(), 'tipo'],
        ];
    }

    private static function estatisticas(): array
    {
        return ['limite' => 1, 'tipo' => 'chegada', 'temporada' => [1], 'flg_sprint' => 'N', 'pilotos_ignorados' => [0], 'equipes_ignoradas' => [0]];
    }

    #[DataProvider('endpoints')]
    public function test_a_union_payload_is_refused_and_reads_nothing(string $uri, array $valid, string $attacked): void
    {
        $payload = $valid;
        data_set($payload, $attacked, $attacked === 'tipo' ? 'chegada, (select password from users limit 1)' : self::PAYLOAD);

        $this->assertRefused($this->postJson($uri, $payload));
    }

    public function test_a_payload_with_the_right_number_of_columns_reads_nothing(): void
    {
        // Three columns, like the query it used to be pasted into: this read every account's email.
        $response = $this->postJson('/ajax/ajaxGetVitoriasPilotoPorTemporada', ['vitoriasPilotosTemporadaId' => '0 UNION SELECT id, 1, email FROM users /*']);

        $this->assertRefused($response);
    }

    #[DataProvider('endpoints')]
    public function test_other_malformed_values_are_refused(string $uri, array $valid, string $attacked): void
    {
        foreach (['1 OR 1=1', '1;', '-1', '1.5', '0x1', ['1', '2']] as $value) {
            $payload = $valid;
            data_set($payload, $attacked, $value);

            $this->postJson($uri, $payload)->assertStatus(422);
        }
    }

    #[DataProvider('endpoints')]
    public function test_the_valid_request_is_accepted(string $uri, array $valid, string $attacked): void
    {
        $this->postJson($uri, $valid)->assertOk();
    }

    private function postRows(string $uri, array $data, string $key): array
    {
        return json_decode(json_encode($this->postJson($uri, $data)->assertOk()->json($key)), true);
    }

    private function tally(array $rows, string $column): array
    {
        $tally = array_map(fn ($r) => [$r['nome'], (int) $r[$column]], $rows);
        usort($tally, fn ($a, $b) => [$b[1], $a[0]] <=> [$a[1], $b[0]]);

        return $tally;
    }

    public function test_the_season_standings_are_unchanged(): void
    {
        $rows = $this->postRows('/ajax/classificacaoGeralPorTemporada', ['temporada_id' => 1], 'resultadosPilotos');
        $this->assertSame([['Max Verstappen', 51], ['Sergio Perez', 50]], array_map(fn ($r) => [$r['nome'], (int) $r['total']], $rows));

        $rows = $this->postRows('/ajax/classificacaoGeralPorTemporada', ['temporada_id' => 1], 'resultadosEquipes');
        $this->assertSame([['Red Bull', 101]], array_map(fn ($r) => [$r['nome'], (int) $r['total']], $rows));

        // A season that belongs to another account yields nothing for this one.
        $this->assertSame([], $this->postRows('/ajax/classificacaoGeralPorTemporada', ['temporada_id' => 3], 'resultadosPilotos'));
    }

    public function test_the_comparison_is_unchanged(): void
    {
        $response = $this->postJson('/ajax/comparativos', ['temporada_id' => 1, 'pilotosId' => [1, 2]])->assertOk();

        $this->assertEquals(51, $response->json('piloto1TotPontos'));
        $this->assertEquals(50, $response->json('piloto2TotPontos'));
        $this->assertEquals(1, $response->json('piloto1Largada'));
        $this->assertEquals(1, $response->json('piloto2Largada'));
    }

    public function test_the_standings_up_to_a_race_are_unchanged(): void
    {
        $up = fn (array $d) => array_map(fn ($r) => [$r['nome'], (int) $r['pontos']], $this->postRows('/temporadas/classificacao', $d, 'dados.pilotos'));

        $this->assertSame([['Max', 25], ['Sergio', 18]], $up(['id' => 1, 'idCorrida' => 1]));
        $this->assertSame([['Max', 51], ['Sergio', 50]], $up(['id' => 1, 'idCorrida' => 2]));
    }

    public function test_the_standings_after_a_race_are_unchanged(): void
    {
        $rows = $this->postRows('/fetch/getClassificacaoAposCorrida', ['temporada_id' => 1, 'corrida_id' => 1], 'resultadosPilotos');
        $this->assertSame([['Max', 25], ['Sergio', 18]], array_map(fn ($r) => [$r['nome'], (int) $r['total']], $rows));
    }

    public function test_the_home_tallies_are_unchanged(): void
    {
        // Ties have no defined order, so tallies are compared sorted by name. No season means all seasons.
        $wins = fn (array $d) => $this->tally($this->postRows('/ajax/ajaxGetVitoriasPilotoPorTemporada', $d, 'totPorPiloto'), 'vitorias');
        $this->assertSame([['Max Verstappen', 1], ['Sergio Perez', 1]], $wins(['vitoriasPilotosTemporadaId' => 1]));
        $this->assertSame([['Max Verstappen', 2], ['Sergio Perez', 1]], $wins(['vitoriasPilotosTemporadaId' => '']));
        $this->assertSame([['Max Verstappen', 1]], $wins(['vitoriasPilotosTemporadaId' => 2]));

        $podiums = fn (array $d) => $this->tally($this->postRows('/ajax/ajaxGetPodiosEquipesPorTemporada', $d, 'totPorEquipe'), 'podios');
        $this->assertSame([['Red Bull', 4]], $podiums(['podiosEquipesTemporadaId' => 1]));

        $arrivals = fn (array $d) => $this->tally($this->postRows('/ajax/ajaxGetChegadasPilotosPorTemporada', $d, 'totPorPiloto'), 'chegadas');
        $this->assertSame([['Max Verstappen', 1], ['Sergio Perez', 1]], $arrivals(['temporada' => 1, 'inicio' => 1, 'fim' => 1]));
        $this->assertSame([['Max Verstappen', 1], ['Sergio Perez', 1]], $arrivals(['temporada' => 1, 'inicio' => 2, 'fim' => 2]));
        $this->assertSame([['Max Verstappen', 1]], $arrivals(['temporada' => 1, 'inicio' => 1, 'fim' => 1, 'tipoCorrida' => 'sprint']));
    }
}
