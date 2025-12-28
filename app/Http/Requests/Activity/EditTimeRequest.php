<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;

class EditTimeRequest extends Request
{

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:255',
            'start_time' => 'required|string|max:50',
            'stop_time' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
            'start_time.required' => '开始时间不能为空',
            'stop_time.required' => '结束时间不能为空',
        ];
    }
}
