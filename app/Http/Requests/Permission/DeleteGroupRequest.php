<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteGroupRequest extends Request
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
            'permissions_group_list.*' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'permissions_group_list.required' => '权限组不能为空',
        ];
    }

    public function passedValidation(): void
    {
        parent::passedValidation();
        $this->merge([
            'permissions_group_list' => array_map(
                fn(string $item) => RequestTool::xxs($item),
                $this->input('permissions_group_list')
            ),
        ]);
    }
}
