<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Both actions of the drivers and teams statistics page read the logged-in user's id. A guest, including a browser
 * whose session expired, is sent to the login page like on every other data page instead of reaching that code.
 */
class EstatisticasPilotosEquipesAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_opening_the_page_is_sent_to_login(): void
    {
        $this->get('/estatisticasPilotosEquipes/index')->assertRedirect(route('login'));
    }

    public function test_a_guest_searching_is_sent_to_login(): void
    {
        $this->post('/estatisticasPilotosEquipes/buscar', ['limite' => '1', 'tipo' => 'chegada', 'flg_sprint' => 'N'])
            ->assertRedirect(route('login'));
    }

    public function test_the_page_search_as_a_guest_is_unauthorized(): void
    {
        // The page sends the search with fetch and accept: application/json, which gets a 401 instead of a redirect.
        $this->postJson('/estatisticasPilotosEquipes/buscar', ['limite' => '1', 'tipo' => 'chegada', 'flg_sprint' => 'N'])
            ->assertUnauthorized();
    }

    public function test_a_logged_in_user_still_gets_the_page(): void
    {
        $user = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'x']);

        $this->actingAs($user)->get('/estatisticasPilotosEquipes/index')->assertOk();
    }
}
