<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * Thin wrapper around pragmarx/google2fa-qrcode — all the raw TOTP/QR mechanics
 * for the "Keamanan Akun (2FA)" page and the post-login verification challenge
 * (see App\Filament\Pages\TwoFactorAuthentication and the two-factor challenge
 * route in routes/web.php) live here instead of scattered across both.
 */
class TwoFactorAuthenticator
{
    protected static function engine(): Google2FA
    {
        return new Google2FA;
    }

    public static function generateSecret(): string
    {
        return self::engine()->generateSecretKey();
    }

    /** An inline SVG data URI — no separate image request/route needed. */
    public static function qrCodeSvg(User $user, string $secret): string
    {
        return self::engine()->getQRCodeInline(
            config('app.name', 'Al-Ihsan CMS'),
            $user->email,
            $secret,
        );
    }

    /** One unit of drift tolerance either side, to forgive minor clock skew. */
    public static function verify(string $secret, string $code): bool
    {
        return self::engine()->verifyKey($secret, $code, 1);
    }

    /**
     * @return array<int, string>
     */
    public static function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn (): string => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();
    }
}
