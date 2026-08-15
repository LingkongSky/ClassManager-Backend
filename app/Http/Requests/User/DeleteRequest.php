<?php

namespace App\Http\Requests\User;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $studentList = $this->input('student_list');
        if (is_string($studentList)) {
            $decoded = json_decode($studentList, true);
            $this->merge(['student_list' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function rules(): array
    {
        return [
            'student_list' => 'required|array|min:1|max:500',
            'student_list.*' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'student_list.required' => '用户不能为空',
        ];
    }

    public function passedValidation(): void
    {
        parent::passedValidation();
        $this->merge([
            'student_list' => array_map(
                fn(string $item) => RequestTool::xxs($item),
                $this->input('student_list')
            ),
        ]);
    }
}
