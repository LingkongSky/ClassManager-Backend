<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class SetAdminRequest extends Request
{
    public function rules(): array
    {
        return [
            'admin_list' => 'required|array|max:40960',
        ];
    }

    public function specificMessages(): array
    {
        return [
            'admin_list.required' => '新增管理员不能为空',
        ];
    }

    protected function prepareForValidation()
    {
        // 对内容进行解码 遍历 过滤 数组化
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
            if (isset($item["account"]) && isset($item["permissions_group_id"])) {
                $item["account"] = RequestTool::xxs($item["account"]);
                $item["permissions_group_id"] = RequestTool::xxs($item["permissions_group_id"]);
            }
        }
        $input["admin_list"] = $admin_list;
        $this->replace($input);
    }
}
