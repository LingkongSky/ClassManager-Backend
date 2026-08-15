<?php

namespace App\Http\Requests\Class;

use App\Http\Requests\Request;
use App\Utils\RequestTool;

class GetStudentsByClassesRequest extends Request
{
    public function rules(): array
    {
        return [
            'source_class_ids' => 'required|string|max:9999',
            'query_word' => 'present|nullable|string|max:30',
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

    public function specificMessages(): array
    {
        return [
            'source_class_ids.required' => '来源班级不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();
        $classIds = json_decode($input['source_class_ids'], true);

        if (!is_array($classIds)) {
            $classIds = [];
        }

        $classIds = array_values(array_unique(array_filter(array_map(
            fn($item) => is_string($item) ? RequestTool::xxs($item) : '',
            $classIds
        ))));

        $input['source_class_ids'] = $classIds;

        if (isset($input['query_word']) && is_string($input['query_word'])) {
            $input['query_word'] = RequestTool::xxs($input['query_word']);
        }

        $this->replace($input);
    }
}

