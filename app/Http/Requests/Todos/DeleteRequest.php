<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
        ];
    }

}
