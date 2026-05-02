<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;

class CookieService
{
    public function createAuthCookie(string $token): Cookie
    {
        return new Cookie(
            'BEARER',      // The name of the cookie
            $token,        // The value of the cookie
            time() + (3600 * 24 * 7), // The expiration date (7 days)
            '/',           // The path on the server where the cookie will be available
            null,          // The domain that the cookie is available to
            false,         // Indicates that the cookie should only be transmitted over a secure HTTPS connection
            true,          // Indicates that the cookie will be made accessible only through the HTTP protocol
            false,
            'lax'
        );
    }
}
