<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class EditPasswordRequest extends Request
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|max:200',
            'password' => 'required|string|max:200',
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
