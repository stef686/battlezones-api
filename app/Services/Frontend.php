<?php

namespace App\Services;

/**
 * Links into the SPA.
 *
 * The SPA is a separate origin (ADR-0001), so an emailed link that points at
 * an API route hands its reader raw JSON. Opaque-token links come here; the
 * Laravel-signed ones stay on the API domain, because a signature only
 * validates on the host that generated it, and redirect here once processed.
 *
 * The paths are deliberately short and top-level: a future Universal Links or
 * App Links association has to cover them unambiguously, which it cannot do
 * for a token nested under an Event.
 */
class Frontend
{
    public const INVITE_PATH = '/invites';

    public const FEEDBACK_PATH = '/feedback';

    public const RESET_PASSWORD_PATH = '/reset-password';

    public const EMAIL_VERIFIED_PATH = '/email/verified';

    public const EMAIL_CHANGED_PATH = '/email/changed';

    public const PASSWORD_CHANGED_PATH = '/password/changed';

    /**
     * An absolute URL into the SPA.
     *
     * @param  array<string, string|int>  $query
     */
    public static function url(string $path = '/', array $query = []): string
    {
        $url = rtrim((string) config('app.frontend_url'), '/').'/'.ltrim($path, '/');

        return $query === [] ? $url : $url.'?'.http_build_query($query);
    }

    public static function inviteUrl(string $token): string
    {
        return self::url(self::INVITE_PATH.'/'.$token);
    }

    public static function feedbackUrl(string $token): string
    {
        return self::url(self::FEEDBACK_PATH.'/'.$token);
    }

    public static function resetPasswordUrl(string $token, string $email): string
    {
        return self::url(self::RESET_PASSWORD_PATH, ['token' => $token, 'email' => $email]);
    }

    /**
     * Where a signed API route sends the reader once it has done its work.
     */
    public static function resultUrl(string $path, string $status): string
    {
        return self::url($path, ['status' => $status]);
    }
}
