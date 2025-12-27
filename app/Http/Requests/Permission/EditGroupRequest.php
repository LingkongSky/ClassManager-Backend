<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Request;

class EditGroupRequest extends Request
{
    public function rules(): array
    {
        return [
            'permissions_group_id' => 'required|string|max:200',
            'permissions_group_name' => 'required|string|max:200',
            'class_list' => 'required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions_group_id.required' => '权限组id不能为空',
            'permissions_group_name.required' => '权限组名称不能为空',
            'class_list.required' => '班级不能为空',
        ];
    }
}
