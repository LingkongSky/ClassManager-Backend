<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Request;

class GetCountAdminRequest extends Request
{
    public function rules(): array
    {
        return [
            'query_word' => 'present|nullable|string|max:30',
        ];
    }

    public  function specificMessages(): array
    {
        return [
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
