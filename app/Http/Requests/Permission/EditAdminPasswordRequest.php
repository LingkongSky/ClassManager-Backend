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

    public  function specificMessages(): array
    {
        return [
            'account.required' => '账号不能为空',
            'password.required' => '密码不能为空',
        ];
    }
}
