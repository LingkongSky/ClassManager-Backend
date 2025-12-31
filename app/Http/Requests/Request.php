<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Utils\ResponseTool;
use App\Utils\RequestTool;
use App\Models\Activity;

class Request extends FormRequest
{

    // class_id 权限验证
    protected function authorizeClassId($classId)
    {
        $classId = RequestTool::xxs($classId);
        return $this->user()->isClassInPermisssion($classId);
    }

    // activity_id 权限验证
    protected function authorizeActivityId($activityId)
    {
        $activityId = RequestTool::xxs($this->input($activityId));
        return $this->user()->isClassInPermisssion(Activity::getClass($activityId));
    }

    // activity in class 权限验证
    protected function authorizeActivityInClass($activityId)
    {
        $activityId = RequestTool::xxs($this->input($activityId));
        return ($this->user()->class_id == Activity::getClass($activityId));
    }
    
    // 通用规则
    protected function commonMessages(): array
    {
        return [
            'required' => ':attribute 不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
            'integer' => ':attribute 必须为整数',
            'numeric' => ':attribute 必须为数字',
        ];
    }

    // 消息合并
    public function messages(): array
    {
        return array_merge($this->commonMessages(), $this->specificMessages());
    }

    // 验证失败
    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();
        $response = ResponseTool::jsonResponse(
            1,
            $firstError,
            ['errors' => $validator->errors()->toArray()],
            422
        );
        throw new HttpResponseException($response);
    }

    protected function specificMessages(): array
    {
        return [];
    }


    // 通过验证 进行参数过滤
    public function passedValidation(): void
    {
        $input = $this->all();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = RequestTool::xxs($value);
            }
        }

        $this->replace($input);
    }
}
