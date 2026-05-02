<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;

class CookieService
{
    public function createAuthCookie(string $token): Cookie
    {
        return new Cookie(
            'BEARER',
            $token,
            time() + (3600 * 24 * 7),
            '/',
            null,
            false,
            true,
            false,
            'lax'
        );
    }
}
