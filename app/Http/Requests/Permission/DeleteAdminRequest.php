<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteAdminRequest extends Request
{
    public function rules(): array
    {
        return [
            'admin_list' => 'required|string|max:40960',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_list.required' => '目标不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        $admin_list = json_decode($input["admin_list"], true);

        if ($admin_list === null && json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        if (!is_array($admin_list)) {
            return;
        }

        // 遍历并处理每个元素
        foreach ($admin_list as &$item) {
                $item = RequestTool::xxs($item);
        }

        $input["admin_list"] = $admin_list;
        $this->replace($input);
    }
}
