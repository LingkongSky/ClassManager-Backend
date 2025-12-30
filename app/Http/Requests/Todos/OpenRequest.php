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
        return ($this->user()->class_id == Activity::getClass($activityId)) || $this->user()->isClassInPermisssion(Activity::getClass($activityId));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

}
