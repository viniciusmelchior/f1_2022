<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Races created before corridas.evento_id existed have no event. The race result page and the season
 * classification must answer for them like for any other race, with "-" where the event name goes.
 */
class RacesWithoutEventTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->run();

        $this->user = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => Hash::make('correct-password')]);
        $this->seedSeason($this->user->id);
    }

    /** Inserts a row, filling every NOT NULL column that has no default with a value that fits its type. */
    private function insert(string $table, array $values): void
    {
        $row = [];

        foreach (Schema::getColumns($table) as $column) {
            $name = $column['name'];

            if (array_key_exists($name, $values)) {
                $row[$name] = $values[$name];
            } elseif (! $column['nullable'] && $column['default'] === null && ! $column['auto_increment']) {
                $type = strtolower($column['type_name']);
                $row[$name] = match (true) {
                    str_contains($type, 'int'), str_contains($type, 'bool') => 1,
                    str_contains($type, 'decimal'), str_contains($type, 'float'), str_contains($type, 'double') => 1,
                    str_contains($type, 'datetime'), str_contains($type, 'timestamp') => '2022-01-01 00:00:00',
                    str_contains($type, 'date') => '2022-01-01',
                    default => 'S',
                };
            }
        }

        DB::table($table)->insert($row);
    }

    /** Race 1 has an event, race 2 has none (evento_id NULL), both with results in the same season. */
    private function seedSeason(int $userId): void
    {
        $u = ['user_id' => $userId];

        $this->insert('anos', $u + ['id' => 1, 'ano' => 2022]);
        $this->insert('temporadas', $u + ['id' => 1, 'ano_id' => 1, 'des_temporada' => 'T1', 'flg_finalizada' => 'N']);
        $this->insert('paises', $u + ['id' => 1, 'des_nome' => 'Brasil']);
        $this->insert('pistas', $u + ['id' => 1, 'pais_id' => 1, 'nome' => 'Interlagos']);
        $this->insert('equipes', $u + ['id' => 1, 'nome' => 'Equipe 1', 'pais_id' => 1]);
        $this->insert('equipes', $u + ['id' => 2, 'nome' => 'Equipe 2', 'pais_id' => 1]);
        $this->insert('pilotos', $u + ['id' => 1, 'nome' => 'Piloto', 'sobrenome' => 'Um', 'pais_id' => 1, 'flg_ativo' => 'S']);
        $this->insert('pilotos', $u + ['id' => 2, 'nome' => 'Piloto', 'sobrenome' => 'Dois', 'pais_id' => 1, 'flg_ativo' => 'S']);
        $this->insert('piloto_equipes', $u + ['id' => 1, 'piloto_id' => 1, 'equipe_id' => 1, 'ano_id' => 1]);
        $this->insert('piloto_equipes', $u + ['id' => 2, 'piloto_id' => 2, 'equipe_id' => 2, 'ano_id' => 1]);

        $this->insert('eventos', $u + ['id' => 1, 'des_nome' => 'GP do Brasil', 'pista_id' => 1]);

        foreach ([1 => 1, 2 => null] as $ordem => $eventoId) {
            $this->insert('corridas', $u + [
                'id' => $ordem, 'evento_id' => $eventoId, 'temporada_id' => 1, 'pista_id' => 1,
                'ordem' => $ordem, 'flg_sprint' => 'N', 'volta_rapida' => 1,
            ]);

            foreach ([1, 2] as $pe) {
                $this->insert('resultados', $u + [
                    'corrida_id' => $ordem, 'pilotoEquipe_id' => $pe, 'largada' => $pe, 'chegada' => $pe,
                    'pontuacao' => $pe === 1 ? 25 : 18, 'flg_abandono' => 'N',
                ]);
            }
        }
    }

    public function test_the_fixture_has_a_race_without_an_event(): void
    {
        $this->assertNull(DB::table('corridas')->where('id', 2)->value('evento_id'));
        $this->assertSame(1, (int) DB::table('corridas')->where('id', 1)->value('evento_id'));
    }

    public function test_result_page_of_a_race_without_an_event_renders_a_dash(): void
    {
        $response = $this->actingAs($this->user)->get(route('resultados.show', [2]));

        $response->assertOk();
        $response->assertSee('<h1>-</h1>', false);
        $response->assertSee('Interlagos');
    }

    public function test_result_page_of_a_race_with_an_event_is_unchanged(): void
    {
        $response = $this->actingAs($this->user)->get(route('resultados.show', [1]));

        $response->assertOk();
        $response->assertSee('<h1>GP do Brasil</h1>', false);
        $response->assertDontSee('<h1>-</h1>', false);
        $response->assertSee('Interlagos');
    }

    public function test_season_classification_of_a_race_without_an_event_answers_with_a_dash(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('post.temporadas.classificacao'), ['id' => 1, 'idCorrida' => 2]);

        $response->assertOk();
        $response->assertJsonPath('message', 'OK');
        $response->assertJsonPath('dados.evento', '-');
        $response->assertJsonPath('dados.circuito', 'Interlagos');
        $response->assertJsonPath('dados.ordem', 2);
        $this->assertCount(2, $response->json('dados.pilotos'));
        $this->assertCount(2, $response->json('dados.equipes'));
    }

    public function test_season_classification_defaults_to_the_last_race_when_it_has_no_event(): void
    {
        // Without idCorrida the page asks for the last race of the season, which is the one without an event.
        $response = $this->actingAs($this->user)->postJson(route('post.temporadas.classificacao'), ['id' => 1]);

        $response->assertOk();
        $response->assertJsonPath('dados.evento', '-');
        $response->assertJsonPath('dados.ordem', 2);
    }

    public function test_season_classification_of_a_race_with_an_event_is_unchanged(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('post.temporadas.classificacao'), ['id' => 1, 'idCorrida' => 1]);

        $response->assertOk();
        $response->assertJsonPath('dados.evento', 'GP do Brasil');
        $response->assertJsonPath('dados.circuito', 'Interlagos');
        $response->assertJsonPath('dados.ordem', 1);
        $response->assertJsonPath('dados.contagem_corrida', '1 de 2');
    }
}
