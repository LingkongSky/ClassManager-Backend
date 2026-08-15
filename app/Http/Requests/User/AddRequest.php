<?php

namespace App\Http\Requests\User;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class AddRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $data = $this->input('data');
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded) && isset($decoded['account'], $decoded['name'])) {
                $decoded = [$decoded];
            }
            $this->merge(['data' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function rules(): array
    {
        return [
            'data' => 'required|array|min:1|max:500',
            'data.*' => 'required|array',
            'data.*.name' => 'required|string|max:10',
            'data.*.account' => 'required|string|max:20',
            'data.*.password' => 'required|string|max:99',
            'data.*.class_ids' => 'required|array|min:1',
            'data.*.class_ids.*' => 'required|string|max:20',
            'data.*.primary_class_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'data.required' => '对象不能为空',
        ];
    }

    public function passedValidation(): void
    {
        parent::passedValidation();
        $data = $this->input('data');
        foreach ($data as &$item) {
            $item['name'] = RequestTool::xxs($item['name']);
            $item['account'] = RequestTool::xxs($item['account']);
            $item['password'] = RequestTool::xxs($item['password']);
            $item['class_ids'] = array_values(array_unique(array_map(
                fn(string $classId) => RequestTool::xxs($classId),
                $item['class_ids']
            )));
            $item['primary_class_id'] = RequestTool::xxs($item['primary_class_id']);
        }
        unset($item);
        $this->merge(['data' => $data]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('data', []) as $index => $item) {
                if (is_array($item)
                    && isset($item['primary_class_id'], $item['class_ids'])
                    && is_array($item['class_ids'])
                    && !in_array($item['primary_class_id'], $item['class_ids'], true)) {
                    $validator->errors()->add("data.{$index}.primary_class_id", '主班级必须包含在班级列表中');
                }
            }
        });
    }
}
