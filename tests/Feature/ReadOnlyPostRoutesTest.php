<?php

namespace Tests\Feature;

use App\Http\Middleware\ReadOnlyWhileImpersonating;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The POST routes that stay available while an administrator views the site as another user fetch tables and
 * charts. They run against the application's real schema here, with a small season, to show that each one works
 * in a view and writes nothing.
 */
class ReadOnlyPostRoutesTest extends TestCase
{
    private const PASSWORD = 'correct-password';

    private ?string $sessionId = null;

    private User $admin;

    private User $ana;

    /** @var list<string> */
    private array $writes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->run();
        config(['admin.emails' => ['admin@example.com']]);

        $this->admin = User::create(['name' => 'Vinicius', 'email' => 'admin@example.com', 'password' => Hash::make(self::PASSWORD)]);
        $this->ana = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => Hash::make(self::PASSWORD)]);

        $this->seedSeason($this->ana->id);

        $this->visit('POST', route('user.authenticate'), ['email' => 'admin@example.com', 'password' => self::PASSWORD])->assertRedirect();
        $this->visit('POST', route('usuarios.entrarComo', [$this->ana->id]))->assertRedirect(route('home'));

        // Record every statement that is not a select, from here on. This runs before the guard of the middleware,
        // which is registered later, so it sees a statement the guard is about to refuse as well as one that runs.
        DB::beforeExecuting(function (string $query) {
            if (! preg_match('/^\s*select\b/i', $query)) {
                $this->writes[] = $query;
            }
        });
    }

    private function visit(string $method, string $uri, array $data = []): TestResponse
    {
        Auth::forgetGuards();

        if ($this->sessionId !== null) {
            $this->withCookie(config('session.cookie'), $this->sessionId);
        }

        $response = $this->call($method, $uri, $data);

        $this->sessionId = $this->app['session.store']->getId();

        return $response;
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

        foreach ([1, 2] as $ordem) {
            $this->insert('corridas', $u + ['id' => $ordem, 'evento_id' => 1, 'temporada_id' => 1, 'pista_id' => 1, 'ordem' => $ordem, 'flg_sprint' => 'N', 'volta_rapida' => 1]);

            foreach ([1, 2] as $pe) {
                $this->insert('resultados', $u + [
                    'corrida_id' => $ordem, 'pilotoEquipe_id' => $pe, 'largada' => $pe, 'chegada' => $pe,
                    'pontuacao' => $pe === 1 ? 25 : 18, 'flg_abandono' => 'N',
                ]);
            }
        }
    }

    /**
     * Route name => the body the page sends. Every route in READ_ONLY_POST_ROUTES must appear here.
     *
     * @return array<string, array<string, mixed>>
     */
    private function requests(): array
    {
        $home = ['inicio' => 2022, 'fim' => 2022, 'tipo' => 'ambos', 'tipoCorrida' => 'ambos', 'pais_id' => 1, 'temporada_id' => 1];

        return [
            'ajax.ajaxGetChegadasEquipesPorTemporada' => ['temporada' => 1] + $home,
            'ajax.ajaxGetChegadasPilotosPorTemporada' => ['temporada' => 1] + $home,
            'ajax.ajaxGetPodiosEquipesPorTemporada' => ['podiosEquipesTemporadaId' => 1],
            'ajax.ajaxGetPodiosPilotoPorTemporada' => ['podiosPilotosTemporadaId' => 1],
            'ajax.ajaxGetPolesEquipesPorTemporada' => ['polesEquipesTemporadaId' => 1],
            'ajax.ajaxGetPolesPilotosPorTemporada' => ['polesPilotosTemporadaId' => 1],
            'ajax.ajaxGetVitoriasEquipesPorTemporada' => ['vitoriasEquipesTemporadaId' => 1],
            'ajax.ajaxGetVitoriasPilotoPorTemporada' => ['vitoriasPilotosTemporadaId' => 1],
            'url_chegada_equipes' => $home + ['anoInicioChegadaEquipes' => 2022, 'anoFimChegadaEquipes' => 2022],
            'url_chegada_pilotos' => $home + ['anoInicioChegadaPilotos' => 2022, 'anoFimChegadaPilotos' => 2022],
            'url_largada_equipes' => $home + ['anoInicioLargadaEquipes' => 2022, 'anoFimLargadaEquipes' => 2022],
            'url_largada_pilotos' => $home + ['anoInicioLargadaPilotos' => 2022, 'anoFimLargadaPilotos' => 2022],
            'buscaResultadosCorrida' => ['temporada' => 1, 'busca' => '', 'flg_sprint' => 'N', 'qtdResultados' => 10],
            'ajax.ajaxGetStatsEquipePorTemporada' => ['equipe_id' => 1, 'temporada_id' => 1],
            'ajax.ajaxGetStatsPilotoPorTemporada' => ['piloto_id' => 1, 'temporada_id' => 1],
            'ajax.classificacaoGeralPorTemporada' => ['temporada_id' => 1],
            'ajax.comparativos' => ['pilotosId' => [1, 2], 'temporada_id' => 1],
            'ajax.getPilotosPorTemporada' => ['temporada_id' => 1],
            'fetch.getClassificacaoAposCorrida' => ['temporada_id' => 1, 'corrida_id' => 1],
            'post.temporadas.classificacao' => ['id' => 1, 'idCorrida' => 1],
            'paises.ajaxGetChegadasEquipes' => $home,
            'paises.ajaxGetChegadasPilotos' => $home,
            'paises.ajaxGetLargadasEquipes' => $home,
            'paises.ajaxGetLargadasPilotos' => $home,
            'buscaPerformancePorPista' => ['idPiloto' => 1, 'idPista' => 1],
            'estatisticas.pilotos.equipes.buscar' => ['temporada' => ['1'], 'tipo' => 'chegada', 'flg_sprint' => 'N', 'limite' => 1, 'pilotos_ignorados' => ['0'], 'equipes_ignoradas' => ['0']],
        ];
    }

    public function test_every_listed_route_has_a_request_here_and_exists(): void
    {
        $listed = ReadOnlyWhileImpersonating::READ_ONLY_POST_ROUTES;

        $this->assertEqualsCanonicalizing($listed, array_keys($this->requests()));

        foreach ($listed as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "$name is not a route");
            $this->assertContains('POST', $route->methods(), "$name is not a POST route");
        }
    }

    public function test_each_listed_route_answers_while_viewing_and_writes_nothing(): void
    {
        $this->assertSame([], $this->writes);

        foreach ($this->requests() as $name => $body) {
            $response = $this->visit('POST', route($name), $body);

            $this->assertSame(200, $response->getStatusCode(), "$name answered ".$response->getStatusCode().' '.($response->exception?->getMessage() ?? ''));
            $response->assertSessionMissing('impersonation_blocked');
            // Some actions answer 200 with an error message when anything fails, a refused write included.
            $this->assertStringNotContainsString('Erro na requisição', $response->getContent(), "$name reported an error");
        }

        $this->assertSame([], $this->writes, 'a listed route tried to write to the database');
    }

    public function test_the_write_guard_stops_a_listed_route_that_starts_writing(): void
    {
        // A route that carries a listed name but writes: the database refuses it, and nothing is stored.
        Route::middleware('web')->post('/zz-write-attempt', function () {
            DB::table('anos')->insert(['id' => 99, 'user_id' => 2, 'ano' => 1999, 'flg_ativo' => 'S']);

            return 'wrote';
        })->name('buscaResultadosCorrida');

        $response = $this->visit('POST', '/zz-write-attempt');

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(0, DB::table('anos')->where('id', 99)->count());
    }

    public function test_the_guard_is_off_again_once_the_request_is_over(): void
    {
        $this->visit('POST', route('buscaResultadosCorrida'), $this->requests()['buscaResultadosCorrida'])->assertOk();

        // Writes outside a listed request are not touched by the guard (the session itself is saved after it).
        DB::table('anos')->insert(['id' => 98, 'user_id' => 2, 'ano' => 1998, 'flg_ativo' => 'S']);
        $this->assertSame(1, DB::table('anos')->where('id', 98)->count());
    }

    public function test_a_write_route_is_still_rejected_while_viewing(): void
    {
        foreach (['pilotos.store', 'corridas.store', 'resultados.update', 'pilotoEquipe.deleteSelecionados', 'user.logout', 'usuarios.entrarComo'] as $name) {
            $uri = route($name, ['id' => 1]);
            $response = $this->visit('POST', $uri, ['nome' => 'x']);

            $response->assertRedirect();
            $response->assertSessionHas('impersonation_blocked');
        }

        $this->assertSame([], $this->writes);
        $this->assertSame(0, DB::table('pilotos')->where('nome', 'x')->count());
    }

    public function test_every_other_post_route_is_rejected_while_viewing(): void
    {
        $listed = ReadOnlyWhileImpersonating::READ_ONLY_POST_ROUTES;

        $others = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('POST', $route->methods(), true))
            ->reject(fn ($route) => in_array($route->getName(), $listed, true) || str_starts_with($route->uri(), '_ignition') || $route->getName() === 'usuarios.voltar')
            ->map(fn ($route) => '/'.ltrim(preg_replace('/\{[^}]+\??\}/', '1', $route->uri()), '/'));

        $this->assertGreaterThanOrEqual(15, $others->count());

        foreach ($others as $uri) {
            $this->visit('POST', $uri)->assertRedirect()->assertSessionHas('impersonation_blocked');
        }

        $this->assertSame([], $this->writes);
    }
}
