<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Counts failed logins per username and per username + client address.
 *
 * Both counters key on the normalized username, so a forged client address
 * does not reset the per-username count. There is deliberately no counter on
 * the address alone: behind the shared load balancer the address cannot be
 * trusted yet. Until the proxy chain is trusted, Request::ip() is the
 * proxy's address, which makes the pair counter equal to a per-username
 * counter with the smaller allowance.
 */
final class LoginThrottle
{
    public const PAIR_MAX_ATTEMPTS = 5;
    public const PAIR_DECAY_SECONDS = 60;
    public const USER_MAX_ATTEMPTS = 20;
    public const USER_DECAY_SECONDS = 900;

    private readonly string $userKey;
    private readonly string $pairKey;

    public function __construct(string $username, ?string $ip)
    {
        $username = self::normalize($username);

        $this->userKey = 'login-throttle:user:'.sha1($username);
        $this->pairKey = 'login-throttle:pair:'.sha1($username.'|'.$ip);
    }

    public static function for(Request $request): self
    {
        return new self((string) $request->input('email'), $request->ip());
    }

    public static function normalize(string $username): string
    {
        return Str::lower(trim($username));
    }

    /** Seconds until another attempt is allowed; 0 when it is allowed now. */
    public function availableIn(): int
    {
        $seconds = 0;

        if (RateLimiter::tooManyAttempts($this->pairKey, self::PAIR_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($this->pairKey);
        }

        if (RateLimiter::tooManyAttempts($this->userKey, self::USER_MAX_ATTEMPTS)) {
            $seconds = max($seconds, RateLimiter::availableIn($this->userKey));
        }

        return $seconds;
    }

    public function hit(): void
    {
        RateLimiter::hit($this->pairKey, self::PAIR_DECAY_SECONDS);
        RateLimiter::hit($this->userKey, self::USER_DECAY_SECONDS);
    }

    public function clear(): void
    {
        RateLimiter::clear($this->pairKey);
        RateLimiter::clear($this->userKey);
    }
}
