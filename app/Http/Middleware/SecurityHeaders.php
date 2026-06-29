<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->isProduction()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        $response->headers->set('Permissions-Policy', $this->permissionsPolicy());
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $isProduction = app()->isProduction();
        $viteOrigins = $isProduction ? '' : ' http://localhost:5173 http://localhost:5174';
        $viteWs = $isProduction ? '' : ' ws://localhost:5173 ws://localhost:5174';

        $imgSrc = "img-src 'self' data: https://avatars.laravel.cloud https://chirper.laravel.cloud";
        if ($storageOrigin = $this->storageOrigin()) {
            $imgSrc .= ' '.$storageOrigin;
        }

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://challenges.cloudflare.com".$viteOrigins,
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net".$viteOrigins,
            $imgSrc,
            "font-src 'self' data: https://fonts.bunny.net".$viteOrigins,
            "connect-src 'self' https://challenges.cloudflare.com".$viteOrigins.$viteWs,
            'frame-src https://challenges.cloudflare.com',
            "form-action 'self' https://checkout.stripe.com https://billing.stripe.com",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
        ];

        if ($isProduction) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    /**
     * Scheme + host of the configured object-storage disk (e.g. the R2 bucket
     * URL), so uploaded images aren't blocked by the img-src CSP directive.
     */
    private function storageOrigin(): ?string
    {
        $url = config('filesystems.disks.s3.url');

        if (! $url) {
            return null;
        }

        $parts = parse_url($url);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'];
    }

    private function permissionsPolicy(): string
    {
        return implode(', ', [
            'accelerometer=()',
            'autoplay=()',
            'camera=()',
            'display-capture=()',
            'geolocation=()',
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'midi=()',
            'payment=()',
            'usb=()',
        ]);
    }
}
