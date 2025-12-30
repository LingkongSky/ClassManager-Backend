<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Utils\RequestTool;
use App\Models\Activity;

class DeleteAdminRequest extends Request
{

    public function authorize()
    {
        $activityId = RequestTool::xxs($this->input('activity_id'));
        return $this->user()->isClassInPermisssion(Activity::getClass($activityId));
    }


    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
            'target_account' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
            'target_account.required' => '目标账号不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

}
