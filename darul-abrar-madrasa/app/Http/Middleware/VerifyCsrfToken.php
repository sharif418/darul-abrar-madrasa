<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * Keep this list minimal; add only if absolutely necessary.
     */
    protected $except = [
        // e.g. 'webhook/*',
    ];
}
