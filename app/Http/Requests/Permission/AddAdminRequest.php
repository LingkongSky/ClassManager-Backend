<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class AddAdminRequest extends Request
{
    public function rules(): array
    {
        return [
            'data' => 'required|string|max:40960',
            'data.*.name' => 'required|string|max:10',
            'data.*.account' => 'required|string|max:20',
            'data.*.password' => 'required|string|min:2|max:99',
            'data.*.class_id' => 'nullable|string|max:20',
            'data.*.permissions_group_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'data.required' => '数据不能为空',
            'data.*.name.required' => '姓名不能为空',
            'data.*.name.max' => '姓名长度不能超过10个字符',
            'data.*.account.required' => '账号不能为空',
            'data.*.account.max' => '账号长度不能超过20个字符',
            'data.*.password.required' => '密码不能为空',
            'data.*.password.min' => '密码长度不能少于2个字符',
            'data.*.password.max' => '密码长度不能超过99个字符',
            'data.*.class_id.max' => '班级ID长度不能超过20个字符',
            'data.*.permissions_group_id.required' => '权限组不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        // 解码前端发送的JSON数据
        $data = json_decode($input["data"], true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->replace(['data' => []]);
            return;
        }

        if (!is_array($data)) {
            $this->replace(['data' => []]);
            return;
        }

        // 处理每个管理员数据
        foreach ($data as &$admin) {
            // XSS过滤
            $admin["name"] = RequestTool::xxs($admin["name"]);
            $admin["account"] = RequestTool::xxs($admin["account"]);
            if (isset($admin["class_id"])) {
                $admin["class_id"] = RequestTool::xxs($admin["class_id"]);
            }
            $admin["permissions_group_id"] = RequestTool::xxs($admin["permissions_group_id"]);
        }

        $input["data"] = $data;
        $this->replace($input);
    }

    /**
     * 验证后获取格式化数据
     */
    public function getFormattedData(): array
    {
        $data = $this->input('data', []);
        return is_array($data) ? $data : [];
    }
}
