<?php

namespace App\Http\Requests\Class;

use App\Http\Requests\Request;

class GetClassRequest extends Request
{
    public function rules(): array
    {
        return [
            'query_count' => 'required|integer|max:100',
            'query_word' => 'present|nullable|string|max:30',
            'sort_mode' => 'sometimes|string|max:10',
            'sort_field' => 'sometimes|string|max:30',
        ];
    }

    public function messages(): array
    {
        return [
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
