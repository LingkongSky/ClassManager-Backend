<?php

namespace App\Http\Requests\User;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules(): array
    {
        return [
            'student_list' => 'required|string|max:40960',
        ];
    }

    public function messages(): array
    {
        return [
            'student_list.required' => '用户不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        $student_list = json_decode($input["student_list"], true);

        if ($student_list === null && json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        if (!is_array($student_list)) {
            return;
        }

        // 遍历并处理每个元素
        foreach ($student_list as &$item) {
                $account = RequestTool::xxs($item);
                $item = $account;
        }

        $input["student_list"] = $student_list;
        $this->replace($input);
    }
}
