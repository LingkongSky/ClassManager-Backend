<?php

namespace App\Http\Requests\Activity;

use App\Utils\RequestTool;
use App\Http\Requests\Request;
use App\Models\Activity;

class EditTimeRequest extends Request
{
    public function authorize()
    {
        return parent::authorizeActivityId($this->input('activity_id'));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
            'start_time' => 'required|string|max:99|date_format:Y-m-d H:i:s',
            'stop_time' => 'required|string|max:99|date_format:Y-m-d H:i:s|after:start_time',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
            'start_time.required' => '开始时间不能为空',
            'stop_time.required' => '结束时间不能为空',
        ];
    }
}
