<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Request;

class EditAdminRequest extends Request
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|max:20',
            'name' => 'required|string|max:10',
            'class_id' => 'present|nullable|max:20',
            'permissions_group_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'account.required' => '账号不能为空',
            'name.required' => '姓名不能为空',
            'class_id.required' => '班级不能为空',
            'permissions_group_id.required' => '权限组不能为空',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'class_id' => $this->has('class_id') && $this->class_id === ''
                ? null
                : $this->class_id
        ]);
    }
}
