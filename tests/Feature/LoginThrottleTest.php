<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LoginThrottle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
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
            $table->integer('xp')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => Hash::make('correct-password')]);
        User::create(['name' => 'Bia', 'email' => 'bia@example.com', 'password' => Hash::make('correct-password')]);
    }

    private function attempt(string $email, string $password = 'wrong', string $ip = '203.0.113.10')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('user.authenticate'), ['email' => $email, 'password' => $password]);
    }

    private function failTimes(int $times, string $email = 'ana@example.com', string $ip = '203.0.113.10'): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->attempt($email, 'wrong', $ip)->assertRedirect();
        }
    }

    public function test_login_rules_are_unchanged_below_the_limit(): void
    {
        $this->attempt('ana@example.com')->assertSessionHasErrors('erro');
        $this->assertGuest();

        $this->attempt('ana@example.com', 'correct-password')->assertRedirect(route('landingPage'));
        $this->assertAuthenticated();
    }

    public function test_the_same_username_and_address_is_blocked_after_five_failures(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS);

        $response = $this->attempt('ana@example.com');

        $response->assertStatus(429);
        $response->assertSee('Muitas tentativas de login');
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
        $this->assertLessThanOrEqual(LoginThrottle::PAIR_DECAY_SECONDS, (int) $response->headers->get('Retry-After'));
    }

    public function test_a_blocked_username_rejects_the_correct_password_too(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS);

        $this->attempt('ana@example.com', 'correct-password')->assertStatus(429);
        $this->assertGuest();
    }

    public function test_another_username_is_not_affected(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS);

        $this->attempt('bia@example.com', 'correct-password')->assertRedirect(route('landingPage'));
        $this->assertAuthenticated();
    }

    public function test_case_and_spacing_do_not_open_a_fresh_counter(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS - 1);

        $this->attempt('ANA@Example.com')->assertRedirect();
        $this->attempt('ana@example.com')->assertStatus(429);
    }

    public function test_spellings_of_an_unknown_email_share_one_counter(): void
    {
        $this->attempt('zed@example.com')->assertRedirect();
        $this->attempt('ZED@example.com')->assertRedirect();
        $this->attempt('zéd@example.com')->assertRedirect();
        $this->attempt('zed@exämple.com')->assertRedirect();
        $this->attempt('zed@example.com')->assertRedirect();

        $this->attempt('zed@example.com')->assertStatus(429);
    }

    public function test_the_limit_is_counted_before_the_password_is_checked(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS - 1);

        // The fifth attempt is still checked, and a correct password logs in.
        $this->attempt('ana@example.com', 'correct-password')->assertRedirect(route('landingPage'));
    }

    public function test_the_pair_limit_clears_after_its_window(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS);
        $this->attempt('ana@example.com')->assertStatus(429);

        $this->travel(LoginThrottle::PAIR_DECAY_SECONDS + 1)->seconds();

        $this->attempt('ana@example.com', 'correct-password')->assertRedirect(route('landingPage'));
    }

    public function test_a_successful_login_resets_the_counters(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS - 1);
        $this->attempt('ana@example.com', 'correct-password')->assertRedirect(route('landingPage'));
        auth()->logout();

        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS - 1);
        $this->attempt('ana@example.com')->assertRedirect();
    }

    public function test_changing_the_client_address_does_not_bypass_the_username_limit(): void
    {
        for ($i = 0; $i < LoginThrottle::USER_MAX_ATTEMPTS; $i++) {
            $this->attempt('ana@example.com', 'wrong', '198.51.100.'.($i + 1))->assertRedirect();
        }

        $this->attempt('ana@example.com', 'correct-password', '192.0.2.99')->assertStatus(429);
    }

    public function test_the_username_limit_clears_after_its_window(): void
    {
        for ($i = 0; $i < LoginThrottle::USER_MAX_ATTEMPTS; $i++) {
            $this->attempt('ana@example.com', 'wrong', '198.51.100.'.($i + 1));
        }
        $this->attempt('ana@example.com', 'correct-password', '192.0.2.99')->assertStatus(429);

        $this->travel(LoginThrottle::USER_DECAY_SECONDS + 1)->seconds();

        $this->attempt('ana@example.com', 'correct-password', '192.0.2.99')->assertRedirect(route('landingPage'));
    }

    public function test_one_address_failing_for_one_username_does_not_block_that_username_elsewhere(): void
    {
        $this->failTimes(LoginThrottle::PAIR_MAX_ATTEMPTS, 'ana@example.com', '203.0.113.10');

        $this->attempt('ana@example.com', 'correct-password', '203.0.113.77')->assertRedirect(route('landingPage'));
    }

    public function test_there_is_no_limit_on_the_address_alone(): void
    {
        foreach (['ana@example.com', 'bia@example.com', 'cris@example.com', 'dani@example.com', 'edu@example.com', 'fabi@example.com'] as $email) {
            $this->attempt($email, 'wrong', '203.0.113.10')->assertRedirect();
        }

        $this->attempt('bia@example.com', 'correct-password', '203.0.113.10')->assertRedirect(route('landingPage'));
    }
}
