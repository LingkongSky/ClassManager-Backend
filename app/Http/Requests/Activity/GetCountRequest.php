<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;
use App\Utils\RequestTool;

class GetCountRequest extends Request
{

    public function authorize()
    {
        $classId = RequestTool::xxs($this->input('class_id'));
        return $this->user()->isClassInPermisssion($classId);
    }

    public function rules(): array
    {
        return [
            'query_word' => 'present|nullable|string|max:30',
            'class_id' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
            'query_word.required' => '搜索条件不能为空',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'query_word' => $this->has('query_word') && $this->query_word === ''
                ? null
                : $this->query_word
        ]);
    }
}
