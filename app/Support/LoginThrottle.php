<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Limits login attempts per account and per account + client address.
 *
 * Each attempt is counted before the password is checked, so a burst of
 * requests cannot all pass a check made before any of them is recorded (the
 * file cache increments without a lock, so a burst can still lose a few
 * counts), and a successful login clears the counters. Both counters key on the account the
 * database resolves the submitted email to, not on the submitted string, so
 * spellings the database treats as equal (case, accents, width) share one
 * count; an email that matches no account is keyed on its ASCII-folded form.
 * A forged client address does not reset the per-account count.
 *
 * There is deliberately no counter on the address alone: behind the shared
 * load balancer the address cannot be trusted yet. Until the proxy chain is
 * trusted, Request::ip() is the proxy's address, which makes the pair counter
 * a per-account counter with the smaller allowance.
 */
final class LoginThrottle
{
    public const PAIR_MAX_ATTEMPTS = 5;
    public const PAIR_DECAY_SECONDS = 60;
    public const USER_MAX_ATTEMPTS = 20;
    public const USER_DECAY_SECONDS = 900;

    private readonly string $userKey;
    private readonly string $pairKey;

    public function __construct(string $account, ?string $ip)
    {
        $this->userKey = 'login-throttle:user:'.sha1($account);
        $this->pairKey = 'login-throttle:pair:'.sha1($account.'|'.$ip);
    }

    public static function for(Request $request, string $email): self
    {
        $userId = User::where('email', $email)->value('id');

        $account = $userId !== null
            ? 'id:'.$userId
            : 'email:'.Str::ascii(Str::lower(trim($email)));

        return new self($account, $request->ip());
    }

    /**
     * Counts one attempt. Returns the seconds to wait when the attempt exceeds
     * a limit, 0 when it may go on to the password check.
     */
    public function attempt(): int
    {
        $pair = RateLimiter::hit($this->pairKey, self::PAIR_DECAY_SECONDS);
        $user = RateLimiter::hit($this->userKey, self::USER_DECAY_SECONDS);

        $seconds = 0;

        if ($pair > self::PAIR_MAX_ATTEMPTS) {
            $seconds = RateLimiter::availableIn($this->pairKey);
        }

        if ($user > self::USER_MAX_ATTEMPTS) {
            $seconds = max($seconds, RateLimiter::availableIn($this->userKey));
        }

        return $seconds;
    }

    public function clear(): void
    {
        RateLimiter::clear($this->pairKey);
        RateLimiter::clear($this->userKey);
    }
}
