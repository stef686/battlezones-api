<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(self::emailKey($request) ?? 'ip:'.$request->ip()));

        // Reading a token link: opening an Invite, or a feedback form.
        RateLimiter::for('token-read', fn (Request $request): array => self::tokenLimits($request, 30, 120));

        // Acting on one: entering with an Invite, claiming it, submitting feedback.
        RateLimiter::for('token-write', fn (Request $request): array => self::tokenLimits($request, 5, 30));
    }

    /**
     * The address being tried, as one bucket however it is capitalised.
     *
     * Without this, five attempts at ada@example.com and five at
     * Ada@Example.com are ten attempts against one account.
     */
    private static function emailKey(Request $request): ?string
    {
        $email = $request->input('email');

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return 'email:'.mb_strtolower(trim($email));
    }

    /**
     * A budget per token, with a looser IP ceiling behind it.
     *
     * Keying these on the IP alone means everyone on the venue wifi on the
     * morning of an Event shares one small budget and locks each other out
     * while claiming Invites at the door. The token is what is being used,
     * so the token is what gets the budget; the ceiling is what still stops
     * one machine walking through a stolen list of them.
     *
     * The token is hashed into the key: it is a credential, and a cache key
     * is not the place to keep one.
     *
     * @return list<Limit>
     */
    private static function tokenLimits(Request $request, int $perToken, int $perIp): array
    {
        $token = $request->route('token');

        $limits = [Limit::perMinute($perIp)->by('ip:'.$request->ip())];

        if (is_string($token) && $token !== '') {
            array_unshift($limits, Limit::perMinute($perToken)->by('token:'.hash('sha256', $token)));
        }

        return $limits;
    }
}
