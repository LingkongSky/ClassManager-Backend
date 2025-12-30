<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;
use App\Utils\RequestTool;
use App\Models\Activity;

class GetListRequest extends Request
{

    public function authorize()
    {
        $classId = RequestTool::xxs($this->input('class_id'));
        return $this->user()->isClassInPermisssion($classId);
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
        ];
    }
}
