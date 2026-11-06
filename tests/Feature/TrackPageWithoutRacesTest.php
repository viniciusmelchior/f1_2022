<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The track page lists the logged-in user's races on a track, but the controller only checks that some user has a race
 * there. A track whose races all belong to other users reaches the view with an empty list and must still render.
 */
class TrackPageWithoutRacesTest extends TestCase
{
    private const BREADCRUMB = '<li class="breadcrumb-item active" aria-current="page">%s</li>';

    private User $owner;

    private User $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->run();

        $this->owner = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => Hash::make('correct-password')]);
        $this->visitor = User::create(['name' => 'Bia', 'email' => 'bia@example.com', 'password' => Hash::make('correct-password')]);
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

    /** The owner has one season with two races on track 1 (Interlagos), won by "Piloto Um". */
    private function seedOwnerRaces(): void
    {
        $u = ['user_id' => $this->owner->id];

        $this->insert('anos', $u + ['id' => 1, 'ano' => 2022]);
        $this->insert('temporadas', $u + ['id' => 1, 'ano_id' => 1, 'des_temporada' => 'T1', 'flg_finalizada' => 'N']);
        $this->insert('paises', $u + ['id' => 1, 'des_nome' => 'Brasil']);
        $this->insert('pistas', $u + ['id' => 1, 'pais_id' => 1, 'nome' => 'Interlagos']);
        $this->insert('equipes', $u + ['id' => 1, 'nome' => 'Equipe 1', 'pais_id' => 1]);
        $this->insert('pilotos', $u + ['id' => 1, 'nome' => 'Piloto', 'sobrenome' => 'Um', 'pais_id' => 1, 'flg_ativo' => 'S']);
        $this->insert('piloto_equipes', $u + ['id' => 1, 'piloto_id' => 1, 'equipe_id' => 1, 'ano_id' => 1]);

        foreach ([1, 2] as $ordem) {
            $this->insert('corridas', $u + [
                'id' => $ordem, 'evento_id' => null, 'temporada_id' => 1, 'pista_id' => 1,
                'ordem' => $ordem, 'flg_sprint' => 'N', 'volta_rapida' => 1, 'exibir_resultado' => 1,
            ]);
            $this->insert('resultados', $u + [
                'corrida_id' => $ordem, 'pilotoEquipe_id' => 1, 'largada' => 1, 'chegada' => 1,
                'pontuacao' => 25, 'flg_abandono' => 'N',
            ]);
        }
    }

    public function test_track_page_renders_for_a_track_where_the_user_has_no_races(): void
    {
        $this->seedOwnerRaces();

        $response = $this->actingAs($this->visitor)->get(route('pistas.show', [1]));

        $response->assertOk();
        $response->assertSee(sprintf(self::BREADCRUMB, '-'), false);
        $response->assertDontSee('Interlagos');
        $response->assertDontSee('Piloto Um');
        $response->assertDontSee(route('resultados.show', [1]), false);
    }

    public function test_track_page_with_the_users_races_is_unchanged(): void
    {
        $this->seedOwnerRaces();

        $response = $this->actingAs($this->owner)->get(route('pistas.show', [1]));

        $response->assertOk();
        $response->assertSee(sprintf(self::BREADCRUMB, 'Interlagos'), false);
        $response->assertDontSee(sprintf(self::BREADCRUMB, '-'), false);
        $response->assertSee('Piloto Um');
        $response->assertSee(route('resultados.show', [1]), false);
        $response->assertSee(route('resultados.show', [2]), false);
    }

    public function test_track_without_any_race_still_redirects_back_with_the_error(): void
    {
        $this->insert('paises', ['user_id' => $this->owner->id, 'id' => 1, 'des_nome' => 'Brasil']);
        $this->insert('pistas', ['user_id' => $this->owner->id, 'id' => 1, 'pais_id' => 1, 'nome' => 'Interlagos']);

        $response = $this->actingAs($this->owner)->from(route('pistas.index'))->get(route('pistas.show', [1]));

        $response->assertRedirect(route('pistas.index'));
        $response->assertSessionHas('error', 'Não existem corridas cadastradas para a pista selecionada');
    }
}
