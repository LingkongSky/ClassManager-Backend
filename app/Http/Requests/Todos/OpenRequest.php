<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Utils\RequestTool;

class OpenRequest extends Request
{
    public function authorize()
    {
        $activityId = RequestTool::xxs($this->input('activity_id'));
        return parent::authorizeActivityInClass($activityId) || $this->user()->isClassInPermisssion(Activity::getClass($activityId));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
        ];
    }
}
