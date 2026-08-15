<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class AddAdminRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $data = $this->input('data');
        if (is_string($data)) {
            $decoded = json_decode($data, true);
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
            'data.*.password' => 'required|string|min:2|max:99',
            'data.*.class_id' => 'nullable|string|max:20',
            'data.*.class_ids' => 'sometimes|array',
            'data.*.class_ids.*' => 'string|max:20',
            'data.*.primary_class_id' => 'nullable|string|max:20',
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
        parent::passedValidation();
        $data = $this->input('data');

        // 处理每个管理员数据
        foreach ($data as &$admin) {
            // XSS过滤
            $admin["name"] = RequestTool::xxs($admin["name"]);
            $admin["account"] = RequestTool::xxs($admin["account"]);
            if (isset($admin["class_id"])) {
                $admin["class_id"] = RequestTool::xxs($admin["class_id"]);
            }
            $classIds = $admin['class_ids'] ?? [];
            $classIds = array_values(array_unique(array_filter(array_map(
                fn($item) => is_string($item) ? RequestTool::xxs($item) : '',
                $classIds
            ))));
            $admin["class_ids"] = $classIds;
            $admin["primary_class_id"] = RequestTool::xxs($admin["primary_class_id"] ?? '');
            $admin["permissions_group_id"] = RequestTool::xxs($admin["permissions_group_id"]);
        }

        unset($admin);
        $this->merge(['data' => $data]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('data', []) as $index => $item) {
                if (is_array($item)
                    && !empty($item['primary_class_id'])
                    && isset($item['class_ids'])
                    && is_array($item['class_ids'])
                    && !in_array($item['primary_class_id'], $item['class_ids'], true)) {
                    $validator->errors()->add("data.{$index}.primary_class_id", '主班级必须包含在班级列表中');
                }
            }
        });
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
