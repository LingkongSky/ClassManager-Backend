<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:20',
            'account' => 'required|string|max:20',
            'name' => 'required|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
            'account.required' => '学号不能为空',
            'name.required' => '姓名不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

}
