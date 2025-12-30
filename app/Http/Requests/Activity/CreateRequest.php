<?php

namespace App\Http\Requests\Activity;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class CreateRequest extends Request
{

    // TODO 处理文件校验 存放与转移
    public function authorize()
    {
        $classId = RequestTool::xxs($this->input('class_id'));
        return $this->user()->isClassInPermisssion($classId);
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'class_id' => 'required|string|max:255',
            'requestment' => 'required|string|max:4096',
            'rename_format' => 'required|string|max:100',
            'accept' => 'required|string|max:255',
            'start_time' => 'required|string|max:50',
            'stop_time' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '名称不能为空',
            'title.required' => '标题不能为空',
            'class_id.required' => '班级ID不能为空',
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
