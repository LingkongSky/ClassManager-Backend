<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Closure;

class TrustProxies extends Middleware
{

    protected $proxies = [
        '127.0.0.1',
    ];

    protected $headers = Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function handle($request, Closure $next)
    {
        $ip = $this->getRealIp($request);
        if ($ip) {
            $request->server->set('REMOTE_ADDR', $ip);
        }

        return $next($request);
    }

    private function getRealIp($request)
    {
        if ($forwardedFor = $request->header('X-Forwarded-For')) {
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        if ($realIp = $request->header('X-Real-IP')) {
            return $realIp;
        }

        return $request->server->get('REMOTE_ADDR');
    }
}
