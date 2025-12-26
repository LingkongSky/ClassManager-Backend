<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StudentAuth
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // 1. 检查是否有Bearer Token
        if (!$request->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => '未提供认证令牌',
                'data' => null
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 2. 验证token有效性
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => '令牌无效或已过期',
                'data' => null
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}