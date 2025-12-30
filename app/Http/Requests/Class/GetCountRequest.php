<?php

namespace App\Http\Requests\Class;

use App\Http\Requests\Request;

class GetCountRequest extends Request
{
    public function rules(): array
    {
        return [
            'query_word' => 'present|nullable|string|max:30',
        ];
    }

    public function messages(): array
    {
        return [
            'query_word.required' => '搜索条件不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
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
