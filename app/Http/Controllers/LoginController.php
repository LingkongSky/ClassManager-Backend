<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Utils\ResponseTool;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // 检查限流
            $request->ensureIsNotRateLimited();

            // 获取验证后的数据
            $remember = $request->boolean('remember', false);

            // 查找用户
            $student = Student::where('account', $request->input('account'))->first();

            // 验证密码
            if (!$student || Student::hashPassword($request->input('password')) !== $student->password) {
                RateLimiter::hit($request->throttleKey(), 60); // 60秒风控
                throw ValidationException::withMessages([
                    'account' => ['账号或密码错误'],
                ]);
            }

            // 清除登录限制计数
            RateLimiter::clear($request->throttleKey());

            // 创建访问令牌（带过期时间）
            $tokenResult = $student->createTokenWithExpiry($request->input('account'), $remember);

            $expiresAt = $tokenResult->accessToken->expires_at;

            return ResponseTool::jsonResponse(0, '登录成功', [
                'token' => $tokenResult->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt?->toDateTimeString(),
            ]);
        } catch (ValidationException $e) {
            // 如果是验证异常，直接抛出
            throw $e;
        } catch (\Exception $e) {
            return ResponseTool::jsonResponse(
                1,
                '登录失败，请稍后重试',
                config('app.debug') ? $e->getMessage() : null,
                500
            );
        }
    }


    public function me(Request $request)
    {
        $student = $request->user();

        return ResponseTool::jsonResponse(0, '获取用户信息成功', [
            'id' => $student->id,
            'name' => $student->name,
            'account' => $student->account,
            'level' => $student->level,
            'class_id' => $student->class_id,
            'permissions_group_id' => $student->permissions_group_id,
        ]);
    }

    // 登出
    public function logout(Request $request): JsonResponse
    {
        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 获取用户数量
    public function getUserCount(): JsonResponse
    {
        return ResponseTool::jsonResponse(0, 'success', Student::getStudentCount());
    }

    // 登陆失败风控
    private function handleFailedLogin(LoginRequest $request): void
    {
        $throttleKey = Str::transliterate(
            Str::lower($request->input('account')) . '|' . $request->ip()
        );

        RateLimiter::hit($throttleKey, 60);
    }

    // 登录通过 清除风控
    private function clearLoginAttempts(LoginRequest $request): void
    {
        $throttleKey = Str::transliterate(
            Str::lower($request->input('account')) . '|' . $request->ip()
        );

        RateLimiter::clear($throttleKey);
    }
}
