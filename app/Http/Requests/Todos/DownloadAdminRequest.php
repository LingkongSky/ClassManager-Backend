<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Utils\RequestTool;

class DownloadAdminRequest extends Request
{

    public function authorize()
    {
        $activity_id = RequestTool::xxs($this->input('activity_id'));
        return $this->user()->isClassInPermisssion(Activity::getClass($activity_id));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:100',
            'target_account' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
            'target_account.required' => '目标用户不能为空',
        ];
    }
}