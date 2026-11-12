<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Every catalog row (drivers, teams, tracks, countries, years) belongs to one account and is only referenced by rows of
 * that same account. Pages must therefore read the catalog of the logged-in account: a driver page opened by an account
 * other than user 3 used to look the driver up under user 3 and fail with 500.
 */
class CatalogScopedToLoggedInUserTest extends TestCase
{
    private User $outro;

    private User $dono3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->run();

        $this->outro = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => Hash::make('correct-password')]);
        User::create(['name' => 'Bia', 'email' => 'bia@example.com', 'password' => Hash::make('correct-password')]);
        $this->dono3 = User::create(['name' => 'Caio', 'email' => 'caio@example.com', 'password' => Hash::make('correct-password')]);
        $this->assertSame(3, (int) $this->dono3->id);

        // Account 1 owns driver 1 and team 1; account 3 owns driver 2 and team 2.
        $this->seedAccount((int) $this->outro->id, 1, 'Alonso');
        $this->seedAccount((int) $this->dono3->id, 2, 'Barrichello');
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

    /** One season with one race won by the account's only driver; every id equals $n so accounts never share rows. */
    private function seedAccount(int $userId, int $n, string $sobrenome): void
    {
        $u = ['user_id' => $userId];

        $this->insert('anos', $u + ['id' => $n, 'ano' => 2020 + $n]);
        $this->insert('temporadas', $u + ['id' => $n, 'ano_id' => $n, 'des_temporada' => 'T'.$n, 'flg_finalizada' => 'N']);
        $this->insert('paises', $u + ['id' => $n, 'des_nome' => 'Pais '.$sobrenome]);
        $this->insert('pistas', $u + ['id' => $n, 'pais_id' => $n, 'nome' => 'Pista '.$sobrenome]);
        $this->insert('equipes', $u + ['id' => $n, 'nome' => 'Equipe '.$sobrenome, 'pais_id' => $n, 'flg_ativo' => 'S']);
        $this->insert('pilotos', $u + ['id' => $n, 'nome' => 'Piloto', 'sobrenome' => $sobrenome, 'pais_id' => $n, 'flg_ativo' => 'S']);
        $this->insert('piloto_equipes', $u + ['id' => $n, 'piloto_id' => $n, 'equipe_id' => $n, 'ano_id' => $n, 'flg_ativo' => 'S']);
        $this->insert('corridas', $u + [
            'id' => $n, 'evento_id' => null, 'temporada_id' => $n, 'pista_id' => $n,
            'ordem' => 1, 'flg_sprint' => 'N', 'volta_rapida' => $n, 'exibir_resultado' => 1,
        ]);
        $this->insert('resultados', $u + [
            'corrida_id' => $n, 'pilotoEquipe_id' => $n, 'largada' => 1, 'chegada' => 1, 'pontuacao' => 25, 'flg_abandono' => 'N',
        ]);
    }

    public function test_an_account_other_than_user_3_opens_its_own_driver_page(): void
    {
        $response = $this->actingAs($this->outro)->get(route('pilotos.show', [1]));

        $response->assertOk();
        $response->assertSee('Alonso');
    }

    public function test_user_3_still_opens_its_own_driver_page(): void
    {
        $response = $this->actingAs($this->dono3)->get(route('pilotos.show', [2]));

        $response->assertOk();
        $response->assertSee('Barrichello');
    }

    public function test_a_driver_of_another_account_is_not_found(): void
    {
        $this->actingAs($this->outro)->get(route('pilotos.show', [2]))->assertNotFound();
    }

    public function test_an_account_other_than_user_3_opens_its_own_team_page(): void
    {
        $this->actingAs($this->outro)->get(route('equipes.show', [1]))->assertOk()->assertSee('Equipe Alonso');
        $this->actingAs($this->outro)->get(route('equipes.show', [2]))->assertNotFound();
    }

    public function test_driver_and_team_lists_show_only_the_logged_in_account(): void
    {
        $this->actingAs($this->outro)->get(route('pilotos.index'))->assertOk()->assertSee('Alonso')->assertDontSee('Barrichello');
        $this->actingAs($this->outro)->get(route('equipes.index'))->assertOk()->assertSee('Equipe Alonso')->assertDontSee('Equipe Barrichello');
        $this->actingAs($this->dono3)->get(route('pilotos.index'))->assertOk()->assertSee('Barrichello')->assertDontSee('Alonso');
    }

    public function test_catalog_lists_show_only_the_logged_in_account(): void
    {
        $this->actingAs($this->outro)->get(route('pistas.index'))->assertOk()->assertSee('Pista Alonso')->assertDontSee('Pista Barrichello');
        $this->actingAs($this->outro)->get(route('estatisticas.pilotos.equipes.index'))->assertOk()
            ->assertSee('Alonso')->assertDontSee('Barrichello');
    }
}
