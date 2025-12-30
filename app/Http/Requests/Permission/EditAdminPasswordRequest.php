<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Request;

class EditAdminPasswordRequest extends Request
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|max:20',
            'password' => 'required|string|max:99',
        ];
    }

    public function messages(): array
    {
        return [
            'account.required' => '账号不能为空',
            'password.required' => '密码不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

}
