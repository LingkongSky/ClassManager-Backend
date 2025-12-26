<?php

namespace App\Http\Requests\Class;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class EditClassRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:200',
            'class_name' => 'required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级id不能为空',
            'class_name.required' => '班级名不能为空',
        ];
    }

}
