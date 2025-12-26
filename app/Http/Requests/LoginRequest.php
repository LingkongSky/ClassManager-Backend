<?php

namespace App\Http\Requests;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'remember' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'account.required' => '账号不能为空',
            'password.required' => '密码不能为空',
        ];
    }
    
}