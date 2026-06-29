<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private Google2FA $google2FA) {}

    public function generateSecret(): string
    {
        return $this->google2FA->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->google2FA->verifyKey($secret, $code, 1);
    }

    public function otpauthUrl(User $user, string $secret): string
    {
        $issuer = config('app.name', 'Laravel');

        return $this->google2FA->getQRCodeUrl($issuer, $user->email, $secret);
    }

    public function qrCodeSvg(User $user, string $secret, int $size = 240): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($this->otpauthUrl($user, $secret));
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::lower(Str::random(5)).'-'.Str::lower(Str::random(5)))
            ->all();
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->recoveryCodes();
        $index = array_search($code, $codes, true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill([
            'two_factor_recovery_codes' => json_encode(array_values($codes)),
        ])->save();

        return true;
    }
}
