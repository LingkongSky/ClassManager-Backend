<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class AddGroupRequest extends Request
{
    public function rules(): array
    {
        return [
            'permissions_group_list' => 'required|string|max:40960',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions_group_list.required' => '权限组列表不能为空',
            'string' => ':attribute 必须为字符串',
            'max' => ':attribute 不能超过:max个字符',
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
            if (isset($item["name"]) && isset($item["target"])) {
                $item["name"] =  RequestTool::xxs($item["name"]);
                $item["target"] =  RequestTool::xxs($item["target"]);
            }
        }

        $input["permissions_group_list"] = $permissions_group_list;
        $this->replace($input);
    }
}
