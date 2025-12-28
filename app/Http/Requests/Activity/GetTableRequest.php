<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;

class GetTableRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:100',
            'query_count' => 'required|integer|max:100',
            'query_word' => 'present|nullable|string|max:30',
            ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
            'query_count.required' => '查询数量不能为空',
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
