<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetCountRequest extends Request
{
    public function rules(): array
    {
        return [
            'query_word' => 'present|nullable|string|max:30',
            'class_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
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
