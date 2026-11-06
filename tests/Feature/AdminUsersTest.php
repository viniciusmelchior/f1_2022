<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    private const PASSWORD = 'correct-password';

    private ?string $sessionId = null;

    private User $admin;

    private User $ana;

    private User $bia;

    protected function setUp(): void
    {
        parent::setUp();

        // The production database compares emails case-insensitively; SQLite needs NOCASE for the same.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->collation('nocase');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('corridas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });

        config(['admin.emails' => ['admin@example.com', 'second.admin@example.com']]);

        $this->admin = $this->makeUser('Vinicius', 'Admin@Example.com');
        $this->ana = $this->makeUser('Ana', 'ana@example.com');
        $this->bia = $this->makeUser('Bia', 'bia@example.com');

        DB::table('corridas')->insert([['user_id' => $this->ana->id], ['user_id' => $this->ana->id], ['user_id' => $this->bia->id]]);
        DB::table('resultados')->insert([['user_id' => $this->ana->id], ['user_id' => $this->ana->id], ['user_id' => $this->ana->id]]);
    }

    private function makeUser(string $name, string $email): User
    {
        return User::create(['name' => $name, 'email' => $email, 'password' => Hash::make(self::PASSWORD)]);
    }

    /**
     * Sends a request the way a browser does: a fresh authentication guard and the session cookie of the
     * previous response, so what the session stores between requests is what the next request sees.
     */
    private function visit(string $method, string $uri, array $data = [], bool $json = false): TestResponse
    {
        Auth::forgetGuards();

        if ($this->sessionId !== null) {
            $this->withCookie(config('session.cookie'), $this->sessionId);
        }

        $response = $this->call($method, $uri, $data, [], [], $json ? ['HTTP_ACCEPT' => 'application/json'] : []);

        $this->sessionId = $this->app['session.store']->getId();

        return $response;
    }

    private function loginAs(User $user): void
    {
        $this->visit('POST', route('user.authenticate'), ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertRedirect(route('landingPage'));
    }

    private function startViewing(User $target): TestResponse
    {
        return $this->visit('POST', route('usuarios.entrarComo', [$target->id]));
    }

    private function loggedInUserId(): ?int
    {
        Auth::forgetGuards();

        $this->withCookie(config('session.cookie'), $this->sessionId);

        return $this->visit('GET', route('dashboard'))->status() === 200 ? (int) Auth::id() : null;
    }

    // --- configuration and Gate -------------------------------------------------------------------------

    public function test_the_admin_list_is_trimmed_and_case_insensitive(): void
    {
        putenv('F1_ADMIN_EMAILS= Vinicius@Example.com , ,owner@EXAMPLE.com,vinicius@example.com');

        try {
            $emails = (require config_path('admin.php'))['emails'];
        } finally {
            putenv('F1_ADMIN_EMAILS');
        }

        $this->assertSame(['vinicius@example.com', 'owner@example.com'], $emails);
    }

    public function test_the_admin_gate_matches_email_regardless_of_case_and_spacing(): void
    {
        config(['admin.emails' => ['ana@example.com']]);
        $this->ana->email = '  ANA@Example.com ';

        $this->assertTrue(\Gate::forUser($this->ana)->allows('admin'));
        $this->assertFalse(\Gate::forUser($this->bia)->allows('admin'));
    }

    public function test_a_lookalike_email_does_not_fold_into_an_admin_address(): void
    {
        config(['admin.emails' => ['kate@example.com']]);

        // U+212A is the Kelvin sign: mb_strtolower() would turn it into a plain "k".
        $lookalike = $this->makeUser('Lookalike', "\u{212A}ate@example.com");
        $real = $this->makeUser('Kate', 'KATE@example.com');

        $this->assertFalse(\Gate::forUser($lookalike)->allows('admin'));
        $this->assertTrue(\Gate::forUser($real)->allows('admin'));

        putenv("F1_ADMIN_EMAILS=\u{212A}ate@example.com");
        try {
            $this->assertSame(["\u{212A}ate@example.com"], (require config_path('admin.php'))['emails']);
        } finally {
            putenv('F1_ADMIN_EMAILS');
        }
    }

    public function test_an_empty_list_makes_nobody_an_admin(): void
    {
        config(['admin.emails' => []]);

        $this->loginAs($this->admin);
        $this->visit('GET', route('usuarios.index'))->assertForbidden();
    }

    // --- the page ---------------------------------------------------------------------------------------

    public function test_an_admin_sees_the_user_list_with_counts(): void
    {
        $this->loginAs($this->admin);

        $response = $this->visit('GET', route('usuarios.index'));

        $response->assertOk();
        $response->assertSee('Listagem de Usuários');
        $response->assertSee('ana@example.com');
        $response->assertSee('bia@example.com');
        $response->assertSeeInOrder(['Ana', 'ana@example.com', '2', '3']);
        $response->assertSeeInOrder(['Bia', 'bia@example.com', '1', '0']);
    }

    public function test_the_list_does_not_query_per_row(): void
    {
        $this->loginAs($this->admin);

        DB::enableQueryLog();
        $this->visit('GET', route('usuarios.index'))->assertOk();
        $queriesWithThreeUsers = count(DB::getQueryLog());

        foreach (range(1, 12) as $i) {
            $this->makeUser("Extra $i", "extra$i@example.com");
        }

        DB::flushQueryLog();
        $this->visit('GET', route('usuarios.index'))->assertOk();

        $this->assertSame($queriesWithThreeUsers, count(DB::getQueryLog()));
    }

    public function test_a_non_admin_gets_403(): void
    {
        $this->loginAs($this->ana);

        $this->visit('GET', route('usuarios.index'))->assertForbidden();
    }

    public function test_a_guest_is_sent_to_the_login(): void
    {
        $this->visit('GET', route('usuarios.index'))->assertRedirect(route('login'));
        $this->visit('POST', route('usuarios.entrarComo', [$this->ana->id]))->assertRedirect(route('login'));
    }

    public function test_the_sidebar_shows_the_name_and_the_admin_entry_only_to_admins(): void
    {
        $this->loginAs($this->admin);
        $response = $this->visit('GET', route('dashboard'));
        $response->assertSee('Vinicius');
        $response->assertSee('<div class="job">Admin</div>', false);
        $response->assertSee(route('usuarios.index'), false);

        $this->loginAs($this->ana);
        $response = $this->visit('GET', route('dashboard'));
        $response->assertSee('<div class="profile_name">Ana</div>', false);
        $response->assertDontSee('class="job"', false);
        $response->assertDontSee(route('usuarios.index'), false);
    }

    public function test_admins_have_no_view_as_button(): void
    {
        $this->makeUser('Second', 'second.admin@example.com');
        $this->loginAs($this->admin);

        $html = $this->visit('GET', route('usuarios.index'))->getContent();

        $this->assertStringContainsString(route('usuarios.entrarComo', [$this->ana->id]), $html);
        $this->assertStringNotContainsString(route('usuarios.entrarComo', [$this->admin->id]), $html);
        $this->assertStringNotContainsString(route('usuarios.entrarComo', [User::where('name', 'Second')->value('id')]), $html);
    }

    // --- starting and leaving ---------------------------------------------------------------------------

    public function test_an_admin_can_start_viewing_as_a_user_and_leave(): void
    {
        Log::shouldReceive('info')->once()->with('Impersonation started', ['admin_id' => $this->admin->id, 'target_id' => $this->ana->id]);
        Log::shouldReceive('info')->once()->with('Impersonation ended', ['admin_id' => $this->admin->id, 'target_id' => $this->ana->id]);
        Log::shouldReceive('info')->once()->with('Impersonation started', ['admin_id' => $this->admin->id, 'target_id' => $this->bia->id]);
        $this->loginAs($this->admin);
        $sessionBefore = $this->sessionId;

        $this->startViewing($this->ana)->assertRedirect(route('home'));

        $this->assertNotSame($sessionBefore, $this->sessionId, 'the session id is regenerated on start');
        $this->assertSame($this->ana->id, $this->loggedInUserId());

        $sessionWhileViewing = $this->sessionId;

        $this->visit('POST', route('usuarios.voltar'))->assertRedirect(route('usuarios.index'));

        $this->assertNotSame($sessionWhileViewing, $this->sessionId, 'the session id is regenerated on leave');
        $this->assertSame($this->admin->id, $this->loggedInUserId());
        $this->assertNull($this->app['session.store']->get(\App\Support\Impersonation::SESSION_KEY));

        // Back as the admin: the page works and writes are allowed again.
        $this->visit('GET', route('usuarios.index'))->assertOk();
        $this->visit('POST', route('usuarios.entrarComo', [$this->bia->id]))->assertRedirect(route('home'));
    }

    public function test_a_non_admin_cannot_start_viewing_as_anyone(): void
    {
        $this->loginAs($this->ana);

        $this->startViewing($this->bia)->assertForbidden();

        $this->assertSame($this->ana->id, $this->loggedInUserId());
    }

    public function test_an_admin_cannot_view_as_another_admin(): void
    {
        $second = $this->makeUser('Second', 'Second.Admin@example.com');
        $this->loginAs($this->admin);

        $this->startViewing($second)->assertForbidden();

        $this->assertSame($this->admin->id, $this->loggedInUserId());
    }

    public function test_an_admin_cannot_view_as_themselves(): void
    {
        $this->loginAs($this->admin);

        $this->startViewing($this->admin)->assertForbidden();

        $this->assertSame($this->admin->id, $this->loggedInUserId());
    }

    public function test_an_unknown_user_is_a_404(): void
    {
        $this->loginAs($this->admin);

        $this->visit('POST', route('usuarios.entrarComo', [9999]))->assertNotFound();
    }

    public function test_viewing_cannot_be_nested(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        // The read-only rule refuses it; without that rule the admin Gate does, because the user being viewed is never an admin.
        $this->startViewing($this->bia)->assertRedirect();
        $this->assertSame($this->ana->id, $this->loggedInUserId());

        $this->withoutMiddleware(\App\Http\Middleware\ReadOnlyWhileImpersonating::class);
        $this->startViewing($this->bia)->assertForbidden();
        $this->assertSame($this->ana->id, $this->loggedInUserId());
    }

    public function test_leaving_without_viewing_changes_nothing(): void
    {
        Log::spy();
        $this->loginAs($this->ana);

        $this->visit('POST', route('usuarios.voltar'))->assertRedirect(route('home'));

        $this->assertSame($this->ana->id, $this->loggedInUserId());
        Log::shouldNotHaveReceived('info');
    }

    public function test_leaving_when_the_admin_account_is_gone_logs_out(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);
        $this->admin->delete();

        $this->visit('POST', route('usuarios.voltar'))->assertRedirect(route('login'));

        $this->assertNull($this->loggedInUserId());
    }

    public function test_log_entries_carry_only_ids(): void
    {
        Log::spy();
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);
        $this->visit('POST', route('usuarios.voltar'));

        Log::shouldHaveReceived('info')->twice()->withArgs(function (string $message, array $context) {
            return array_keys($context) === ['admin_id', 'target_id'] && is_int($context['admin_id']) && is_int($context['target_id']);
        });
    }

    // --- the banner -------------------------------------------------------------------------------------

    public function test_the_banner_is_on_every_page_while_viewing_and_only_then(): void
    {
        $this->loginAs($this->admin);
        $this->visit('GET', route('dashboard'))->assertDontSee('Voltar para minha conta');

        $this->startViewing($this->ana);

        foreach ([route('dashboard'), route('landingPage')] as $url) {
            $response = $this->visit('GET', $url);
            $response->assertOk();
            $response->assertSee('Você está vendo como');
            $response->assertSee('<strong>Ana</strong>', false);
            $response->assertSee('Voltar para minha conta');
            $response->assertSee('action="'.route('usuarios.voltar').'"', false);
        }

        $this->visit('POST', route('usuarios.voltar'));
        $this->visit('GET', route('dashboard'))->assertDontSee('Voltar para minha conta');
    }

    public function test_the_banner_shows_the_name_escaped(): void
    {
        $evil = $this->makeUser('<script>alert(1)</script>', 'evil@example.com');
        $this->loginAs($this->admin);
        $this->startViewing($evil);

        $this->visit('GET', route('dashboard'))->assertDontSee('<script>alert(1)</script>', false);
    }

    // --- read-only --------------------------------------------------------------------------------------

    public function test_writes_are_rejected_while_viewing(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        // POST and PUT routes exist; a method no route accepts is a 405 before any controller runs.
        $this->visit('POST', '/pilotos/store')->assertRedirect()->assertSessionHas('impersonation_blocked');
        $this->visit('PUT', '/pilotos/update/1')->assertRedirect()->assertSessionHas('impersonation_blocked');
        $this->visit('POST', '/pilotos/update/1', ['_method' => 'PUT'])->assertRedirect()->assertSessionHas('impersonation_blocked');


        $this->assertSame($this->ana->id, $this->loggedInUserId());
    }

    public function test_logout_and_login_are_rejected_while_viewing(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        $this->visit('POST', route('user.logout'))->assertSessionHas('impersonation_blocked');
        $this->visit('POST', route('user.authenticate'), ['email' => $this->bia->email, 'password' => self::PASSWORD])
            ->assertSessionHas('impersonation_blocked');

        $this->assertSame($this->ana->id, $this->loggedInUserId());
    }

    public function test_the_rejection_explains_itself_on_the_next_page(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        $this->visit('POST', '/pilotos/store')->assertRedirect();

        $this->visit('GET', route('dashboard'))->assertSee('Somente leitura: você está vendo como outro usuário');
    }

    public function test_a_rejected_ajax_request_gets_403_json(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        $this->visit('POST', '/ajax/comparativos', [], json: true)
            ->assertForbidden()
            ->assertJsonStructure(['message']);
    }

    public function test_get_requests_still_work_while_viewing(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        $this->visit('GET', route('dashboard'))->assertOk();
        $this->visit('HEAD', route('dashboard'))->assertOk();
    }

    public function test_get_routes_that_change_data_are_rejected_while_viewing(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        $mutating = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->filter(fn ($route) => str_ends_with($route->getActionName(), '@destroy')
                || in_array($route->getActionName(), [
                    \App\Http\Controllers\Site\PistaController::class.'@adicionarAutor',
                    \App\Http\Controllers\Site\PDFController::class.'@index',
                ], true))
            ->map(fn ($route) => '/'.ltrim(preg_replace('/\{[^}]+\??\}/', '1', $route->uri()), '/'));

        $this->assertGreaterThanOrEqual(10, $mutating->count(), 'the delete routes were not found');
        $this->assertContains('/send-email-pdf', $mutating);
        $this->assertContains('/pilotos/delete', $mutating);

        foreach ($mutating as $uri) {
            $this->visit('GET', $uri)->assertRedirect()->assertSessionHas('impersonation_blocked');
        }
    }

    public function test_head_on_a_delete_route_is_rejected_while_viewing(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);

        $this->visit('HEAD', '/pilotos/delete')->assertRedirect()->assertSessionHas('impersonation_blocked');
    }

    public function test_a_regular_page_costs_one_query_for_a_non_admin_as_before(): void
    {
        $this->loginAs($this->ana);

        DB::enableQueryLog();
        $this->visit('GET', route('dashboard'))->assertOk();

        // The user lookup that the `auth` middleware makes; the banner and the `admin` checks add none.
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_the_read_only_rule_does_not_touch_a_regular_session(): void
    {
        $this->loginAs($this->ana);

        $this->visit('POST', '/pilotos/store')->assertSessionMissing('impersonation_blocked');
        $this->visit('GET', route('dashboard'))->assertOk()->assertSessionMissing('impersonation_blocked');
        $this->assertSame($this->ana->id, $this->loggedInUserId());
    }

    /**
     * Every GET route is either rejected while viewing or listed here as read-only after review. A new GET route
     * fails this test until someone decides which it is; one that writes, deletes or sends mail must be rejected
     * in ReadOnlyWhileImpersonating.
     */
    public function test_every_get_route_has_been_reviewed_as_read_only_or_is_rejected(): void
    {
        $rejected = fn ($route) => str_ends_with($route->getActionName(), '@destroy')
            || in_array($route->getActionName(), [
                \App\Http\Controllers\Site\PistaController::class.'@adicionarAutor',
                \App\Http\Controllers\Site\PDFController::class.'@index',
            ], true);

        $readOnly = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->reject($rejected)
            // Routes of vendor packages (dev-only Ignition, Sanctum) are not part of this app's pages.
            ->reject(fn ($route) => str_starts_with($route->uri(), '_ignition') || str_starts_with($route->uri(), 'sanctum/'))
            ->map(fn ($route) => '/'.ltrim($route->uri(), '/'))
            ->unique()->sort()->values()->all();

        $this->assertSame([
            '/',
            '/anos/create',
            '/anos/edit/{id}',
            '/anos/index',
            '/api/user',
            '/condicaoClimatica/create',
            '/condicaoClimatica/edit/{id}',
            '/condicaoClimatica/index',
            '/corridas/adicionar/{id}',
            '/corridas/create',
            '/corridas/edit/{id}/{corrida}',
            '/corridas/index/{id}',
            '/curiosidades',
            '/dashboard',
            '/equipes/create',
            '/equipes/edit/{id}',
            '/equipes/index',
            '/equipes/relacaoForcas/edit/{ano_id}/{temporada_id}',
            '/equipes/relacaoForcas/index',
            '/equipes/relacaoForcas/listagemEquipes/{temporada_id}',
            '/equipes/show/{id}',
            '/estatisticas',
            '/estatisticasPilotosEquipes/index',
            '/estudos',
            '/eventos/create',
            '/eventos/edit/{id}',
            '/eventos/index',
            '/eventos/show/{id}',
            '/home',
            '/paises/create',
            '/paises/edit/{id}',
            '/paises/index',
            '/paises/show/{id}',
            '/pilotoEquipe/create',
            '/pilotoEquipe/edit/{id}',
            '/pilotoEquipe/index',
            '/pilotos/comparativo',
            '/pilotos/create',
            '/pilotos/edit/{id}',
            '/pilotos/export/{id}',
            '/pilotos/index',
            '/pilotos/performance-por-pista',
            '/pilotos/relacaoForcas/edit/{ano_id}/{temporada_id}',
            '/pilotos/relacaoForcas/index',
            '/pilotos/relacaoForcas/listagemPilotos/{temporada_id}',
            '/pilotos/show/{id}',
            '/pistas/create',
            '/pistas/edit/{id}',
            '/pistas/index',
            '/pistas/show/{id}',
            '/register',
            '/resultados/edit/{id}',
            '/resultados/show/{id}',
            '/skin/create',
            '/skin/edit/{id}',
            '/skin/index',
            '/temporadas/classificacao/{id}',
            '/temporadas/create',
            '/temporadas/edit/{id}',
            '/temporadas/index',
            '/temporadas/resultados/{id}/{porPontuacao?}',
            '/tempos',
            '/teste',
            '/users/login',
            '/usuarios/index',
            '/visualizarVitoriasPiloto/{piloto_id}',
        ], $readOnly);
    }

    public function test_viewing_ends_when_the_admin_is_removed_from_the_list(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);
        $this->visit('GET', route('dashboard'))->assertOk();

        config(['admin.emails' => []]);

        $this->visit('GET', route('dashboard'))->assertRedirect(route('login'));
        $this->assertNull($this->loggedInUserId());
    }

    public function test_viewing_ends_when_the_admin_account_is_deleted(): void
    {
        $this->loginAs($this->admin);
        $this->startViewing($this->ana);
        $this->admin->delete();

        $this->visit('GET', route('dashboard'))->assertRedirect(route('login'));
        $this->assertNull($this->loggedInUserId());
    }

    public function test_an_old_entry_cannot_make_another_login_a_way_back_to_the_admin(): void
    {
        // The entry names Ana as the user being viewed, but Bia is the one logged in.
        $this->loginAs($this->bia);
        $this->withSession([\App\Support\Impersonation::SESSION_KEY => ['admin_id' => $this->admin->id, 'target_id' => $this->ana->id]]);
        $this->sessionId = $this->app['session.store']->getId();
        $this->app['session.store']->save();

        $this->visit('GET', route('dashboard'))->assertOk()->assertDontSee('Voltar para minha conta');
        $this->visit('POST', route('usuarios.voltar'))->assertRedirect(route('home'));

        $this->assertSame($this->bia->id, $this->loggedInUserId());
    }
}
