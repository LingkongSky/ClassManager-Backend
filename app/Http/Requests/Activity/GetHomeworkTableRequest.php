<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;
use App\Utils\RequestTool;
use App\Models\Activity;

class GetHomeworkTableRequest extends Request
{

    public function authorize()
    {
        return parent::authorizeActivityId($this->input('activity_id'));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
            'query_count' => 'required|integer|max:100',
            'query_word' => 'present|nullable|string|max:30',
            'sort_mode' => 'sometimes|string|max:10',
            'sort_field' => 'sometimes|string|max:30',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
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
