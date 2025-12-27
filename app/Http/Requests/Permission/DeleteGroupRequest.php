<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteGroupRequest extends Request
{
    public function rules(): array
    {
        return [
            'permissions_group_list' => 'required|string|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions_group_list.required' => '权限组不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        $permissions_group_list = json_decode($input["permissions_group_list"], true);

        if ($permissions_group_list === null && json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        if (!is_array($permissions_group_list)) {
            return;
        }

        // 遍历并处理每个元素
        foreach ($permissions_group_list as &$item) {
                $item  = RequestTool::xxs($item);
        }

        $input["permissions_group_list"] = $permissions_group_list;
        $this->replace($input);
    }
}
