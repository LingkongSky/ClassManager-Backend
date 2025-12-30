<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Utils\RequestTool;

class DownloadRequest extends Request
{
    public function authorize()
    {
        $activityId = RequestTool::xxs($this->input('activity_id'));
        return ($this->user()->class_id == Activity::getClass($activityId));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
        ];
    }
}
