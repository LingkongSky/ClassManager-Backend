<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;

class GetInfoRequest extends Request
{
    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
        ];
    }

}
