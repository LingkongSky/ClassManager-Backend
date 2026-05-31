<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseAuthMiddleware
{
    private const CACHE_PREFIX = 'user_token:';
    private const CACHE_TTL = 3600;

    // 检查权限等级
    protected function checkLevel($user): bool
    {
        return true;
    }

    public function handle(Request $request, Closure $next, $guard = null): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->unauthorizedResponse('未提供认证令牌');
        }

        $tokenHash = hash('sha256', $token);
        $cacheKey = self::CACHE_PREFIX . $tokenHash;

        $user = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($token) {
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken || !$accessToken->tokenable) {
                return null;
            }

            return $accessToken->tokenable;
        });

        if (!$user) {
            return $this->unauthorizedResponse('令牌无效或已过期');
        }

        if (!$this->checkLevel($user)) {
            return $this->unauthorizedResponse('权限不足');
        }

        return $next($request);
    }

    private function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null
        ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}
