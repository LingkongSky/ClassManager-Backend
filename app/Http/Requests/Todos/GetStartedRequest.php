<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;

class GetStartedRequest extends Request
{
    public function rules(): array
    {
        return [
            'query_count' => 'required|string|max:100',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'query_count.required' => '查询数量不能为空',
                    ];
    }
}
