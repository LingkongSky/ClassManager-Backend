<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;

class CreateRequest extends Request
{

    // TODO 处理文件校验 存放与转移
    public function authorize()
    {
        return parent::authorizeClassId($this->input('class_id'));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:20',
            'title' => 'required|string|max:20',
            'class_id' => 'required|string|max:20',
            'requestment' => 'required|string|max:999',
            'rename_format' => 'required|string|max:99',
            'accept' => 'required|string|max:99',
            'start_time' => 'required|string|max:99|date_format:Y-m-d H:i:s',
            'stop_time' => 'required|string|max:99|date_format:Y-m-d H:i:s|after:start_time',
        ];
    }

    public  function specificMessages(): array
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
        ];
    }
}
