<?php

namespace App\Http\Requests\Class;

use App\Http\Requests\Request;
use App\Utils\RequestTool;

class AppendStudentsRequest extends Request
{
    public function rules(): array
    {
        return [
            'target_class_id' => 'required|string|max:20',
            'student_accounts' => 'required|string|max:99999',
        ];
    }

    public function specificMessages(): array
    {
        return [
            'target_class_id.required' => '目标班级不能为空',
            'student_accounts.required' => '学生列表不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();
        $accounts = json_decode($input['student_accounts'], true);

        if (!is_array($accounts)) {
            $accounts = [];
        }

        $accounts = array_values(array_unique(array_filter(array_map(
            fn($item) => is_string($item) ? RequestTool::xxs($item) : '',
            $accounts
        ))));

        $input['target_class_id'] = RequestTool::xxs($input['target_class_id']);
        $input['student_accounts'] = $accounts;
        $this->replace($input);
    }
}

