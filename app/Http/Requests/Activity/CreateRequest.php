<?php

namespace App\Http\Requests\Activity;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class CreateActivityRequest extends Request
{

    // 在此班级进行鉴权
    public function authorize()
    {
        // 先获取用户的权限班级
    /*    $user = $this->user();

        // 再判断class_id中是否所有的班级都在权限班级中
        return $user->isClassInPermisssion(RequestTool::xxs($this->input("class_id")));*/
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'class_id' => 'required|string|max:255',
            'requestment' => 'required|string|max:4096',
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
            'start_time.required' => '开始时间不能为空',
            'stop_time.required' => '结束时间不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

    public function passedValidation(): void
    {/*
        $input = $this->all();

        foreach ($input as $key => $value) {
            if ($key === 'requestment' && is_string($value)) {
                $input[$key] = base64_encode($value);
            } elseif (is_string($value)) {
                $input[$key] = RequestTool::xxs($value);
            }
        }

        $this->replace($input);*/
    }
}
