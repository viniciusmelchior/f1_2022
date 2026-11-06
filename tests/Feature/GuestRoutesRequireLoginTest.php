<?php

namespace Tests\Feature;

use App\Http\Controllers\Site\PDFController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Every route outside the login and registration flow needs a logged-in user. These routes read the user's id or
 * send mail, so a guest used to reach that code (a 500 on most of them, a mail on send-email-pdf) instead of the
 * login page. Each one now sends a guest to login and leaves the logged-in response as it was.
 */
class GuestRoutesRequireLoginTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'x']);
    }

    /**
     * The action sends a mail and ends the request with dd(), which would end the test run too. A stand-in takes its
     * place, so a guest who got past the middleware would fail an assertion instead, and a logged-in user is shown to
     * reach the controller.
     */
    private function replaceSendEmailPdfAction(): void
    {
        $this->app->bind(PDFController::class, fn () => new class extends PDFController {
            public function index()
            {
                return 'reached';
            }
        });
    }

    public function test_estatisticas_sends_a_guest_to_login(): void
    {
        $this->get('/estatisticas')->assertRedirect(route('login'));
    }

    public function test_estatisticas_still_shows_the_page_to_a_logged_in_user(): void
    {
        $this->actingAs($this->user())->get('/estatisticas')->assertOk()->assertViewIs('home.novaHome');
    }

    public function test_busca_resultados_corrida_sends_a_guest_to_login(): void
    {
        $this->post('/buscaResultadosCorrida', ['temporada' => 'todas', 'busca' => '', 'flg_sprint' => 'N', 'qtdResultados' => 10])
            ->assertRedirect(route('login'));
    }

    public function test_busca_resultados_corrida_as_a_guest_page_fetch_is_unauthorized(): void
    {
        // The statistics page sends the search with fetch and accept: application/json, which gets a 401, not a redirect.
        $this->postJson('/buscaResultadosCorrida', ['temporada' => 'todas', 'busca' => '', 'flg_sprint' => 'N', 'qtdResultados' => 10])
            ->assertUnauthorized();
    }

    public function test_busca_resultados_corrida_still_answers_a_logged_in_user(): void
    {
        $this->actingAs($this->user())
            ->postJson('/buscaResultadosCorrida', ['temporada' => 'todas', 'busca' => '', 'flg_sprint' => 'N', 'qtdResultados' => 10])
            ->assertOk()
            ->assertJsonPath('resultadosCorrida.total', 0)
            ->assertJsonPath('resultadosCorrida.data', []);
    }

    public function test_teste_sends_a_guest_to_login(): void
    {
        $this->get('/teste')->assertRedirect(route('login'));
    }

    public function test_teste_is_not_stopped_for_a_logged_in_user(): void
    {
        // HomeController has no teste method, so the route answered 500 to everyone before and still does to a
        // logged-in user: only the guest changes.
        $this->withoutExceptionHandling();
        $this->expectException(\BadMethodCallException::class);

        $this->actingAs($this->user())->get('/teste');
    }

    public function test_pilotos_export_sends_a_guest_to_login(): void
    {
        $this->get('/pilotos/export/1')->assertRedirect(route('login'));
    }

    public function test_pilotos_export_still_downloads_the_sheet_to_a_logged_in_user(): void
    {
        $user = $this->user();
        DB::table('paises')->insert(['id' => 1, 'user_id' => $user->id, 'des_nome' => 'Brasil']);
        DB::table('pilotos')->insert(['id' => 1, 'user_id' => $user->id, 'nome' => 'Piloto', 'sobrenome' => 'Um', 'pais_id' => 1, 'flg_ativo' => 'S']);

        $response = $this->actingAs($user)->get('/pilotos/export/1');

        // The download response deletes its temporary file once it is sent, which a test response never does.
        $this->beforeApplicationDestroyed(fn () => @unlink($response->baseResponse->getFile()->getPathname()));

        $response->assertOk();
        $this->assertMatchesRegularExpression('/Piloto Um_\d{4}\.xlsx/', $response->headers->get('content-disposition'));
    }

    public function test_send_email_pdf_sends_a_guest_to_login_without_sending_mail(): void
    {
        Mail::fake();
        $this->replaceSendEmailPdfAction();

        $this->get('/send-email-pdf')->assertRedirect(route('login'));

        Mail::assertNothingSent();
    }

    public function test_send_email_pdf_still_reaches_its_action_for_a_logged_in_user(): void
    {
        $this->replaceSendEmailPdfAction();

        $this->actingAs($this->user())->get('/send-email-pdf')->assertOk()->assertSee('reached');
    }

    public function test_tempos_sends_a_guest_to_login(): void
    {
        $this->get('/tempos')->assertRedirect(route('login'));
    }

    public function test_tempos_still_shows_the_page_to_a_logged_in_user(): void
    {
        $this->actingAs($this->user())->get('/tempos')->assertOk()->assertViewIs('site.listagemTempos.index');
    }
}
