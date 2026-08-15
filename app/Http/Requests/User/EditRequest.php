<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Utils\RequestTool;

class EditRequest extends Request
{
    protected function prepareForValidation()
    {
        $classIds = $this->input('class_ids');
        if (is_string($classIds)) {
            $decoded = json_decode($classIds, true);
            if (is_array($decoded)) {
                $this->merge(['class_ids' => $decoded]);
            }
        }
    }

    public function passedValidation(): void
    {
        parent::passedValidation();
        $classIds = $this->input('class_ids', []);
        if (is_array($classIds)) {
            $this->merge([
                'class_ids' => array_map(fn($item) => RequestTool::xxs($item), $classIds),
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $classIds = $this->input('class_ids', []);
            $primaryClassId = $this->input('primary_class_id');

            if (is_array($classIds) && count($classIds) === 1 && $primaryClassId !== $classIds[0]) {
                $validator->errors()->add('primary_class_id', '仅有一个班级时，主班级必须与班级一致');
            }
        });
    }

    public function rules(): array
    {
        return [
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'required|string|max:20',
            'primary_class_id' => 'required|string|max:20',
            'account' => 'required|string|max:20',
            'name' => 'required|string|max:10',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'class_ids.required' => '班级不能为空',
            'account.required' => '学号不能为空',
            'name.required' => '姓名不能为空',
        ];
    }
}
