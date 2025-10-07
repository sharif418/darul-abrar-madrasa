<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * You may specify the addresses of proxies that are trusted to send
     * X-Forwarded-* headers. Use "*" to trust all proxies (typical when
     * running behind a load balancer / reverse proxy).
     */
    protected $proxies = '*';

    /**
     * The headers to use to detect proxies.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
