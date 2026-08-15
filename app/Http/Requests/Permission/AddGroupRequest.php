<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class AddGroupRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $groupList = $this->input('permissions_group_list');
        if (is_string($groupList)) {
            $decoded = json_decode($groupList, true);
            $this->merge(['permissions_group_list' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function rules(): array
    {
        return [
            'permissions_group_list' => 'required|array|min:1|max:500',
            'permissions_group_list.*' => 'required|array',
            'permissions_group_list.*.name' => 'required|string|max:40',
            'permissions_group_list.*.target' => 'required|string|max:9999',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'permissions_group_list.required' => '权限组列表不能为空',
        ];
    }

    public function passedValidation(): void
    {
        parent::passedValidation();
        $groupList = array_map(fn(array $item) => [
            'name' => RequestTool::xxs($item['name']),
            'target' => RequestTool::xxs($item['target']),
        ], $this->input('permissions_group_list'));
        $this->merge(['permissions_group_list' => $groupList]);
    }
}
