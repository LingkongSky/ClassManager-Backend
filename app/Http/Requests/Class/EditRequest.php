<?php

namespace App\Http\Requests\Class;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:20',
            'class_name' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
            'class_name.required' => '班级名不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

}
