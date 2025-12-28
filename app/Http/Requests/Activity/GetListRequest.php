<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
        ];
    }

}
