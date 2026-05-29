<?php

namespace App\Http\Requests\Class;

use App\Http\Requests\Request;

class GetClassIdRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_name' => 'required|string|max:30',
        ];
    }

    public function specificMessages(): array
    {
        return [
            'class_name.required' => '班级名称不能为空',
        ];
    }
}
