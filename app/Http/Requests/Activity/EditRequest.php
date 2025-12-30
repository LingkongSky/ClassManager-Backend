<?php

namespace App\Http\Requests\Activity;

use App\Utils\RequestTool;
use App\Http\Requests\Request;
use App\Models\Activity;

class EditRequest extends Request
{

    // TODO 处理文件校验 存放与转移
    public function authorize()
    {
        $activityId = RequestTool::xxs($this->input('activity_id'));
        return $this->user()->isClassInPermisssion(Activity::getClass($activityId));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
            'name' => 'required|string|max:20',
            'title' => 'required|string|max:20',
            'requestment' => 'required|string|max:999',
            'rename_format' => 'required|string|max:99',
            'accept' => 'required|string|max:99',
            'file_upload' => 'required|string|max:50',
            'start_time' => 'required|string|max:99|date_format:Y-m-d H:i:s',
            'stop_time' => 'required|string|max:99|date_format:Y-m-d H:i:s|after:start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '名称不能为空',
            'activity_id.required' => '活动ID不能为空',
            'file_upload.required' => '文件选项不能为空',
            'title.required' => '标题不能为空',
            'requestment.required' => '要求不能为空',
            'accept.required' => '接受条件不能为空',
            'rename_format.required' => '命名格式不能为空',
            'start_time.required' => '开始时间不能为空',
            'stop_time.required' => '结束时间不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }
}
