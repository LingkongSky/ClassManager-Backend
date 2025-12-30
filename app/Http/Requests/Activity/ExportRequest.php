<?php

namespace App\Http\Requests\Activity;

use App\Utils\RequestTool;
use App\Http\Requests\Request;
use App\Models\Activity;

class ExportRequest extends Request
{
    
    public function authorize()
    {
        $activityId = RequestTool::xxs($this->input('activity_id'));
        return $this->user()->isClassInPermisssion(Activity::getClass($activityId));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:100',
            'times' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
            'times.required' => '标记不能为空',
        ];
    }

}
