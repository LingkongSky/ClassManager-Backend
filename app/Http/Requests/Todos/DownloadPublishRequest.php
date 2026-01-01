<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Utils\RequestTool;

class DownloadPublishRequest extends Request
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
            'file_name' => 'required|string|max:255',            
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
            'file_name.required' => '文件名不能为空',
        ];
    }
}
