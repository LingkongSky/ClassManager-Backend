<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends Request
{
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
    }

    public function rules(): array
    {
        return [
            'account' => 'required|string|max:20',
            'password' => 'required|string|max:99',
            'remember' => 'sometimes|boolean',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'account.required' => '账号不能为空',
            'password.required' => '密码不能为空',
        ];
    }



    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'account' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('account')) . '|' . $this->ip());
    }
}
