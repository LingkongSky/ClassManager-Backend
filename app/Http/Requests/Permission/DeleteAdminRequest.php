<?php

namespace App\Http\Requests\Permission;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteAdminRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $adminList = $this->input('admin_list');
        if (is_string($adminList)) {
            $decoded = json_decode($adminList, true);
            $this->merge(['admin_list' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function rules(): array
    {
        return [
            'admin_list' => 'required|array|min:1|max:500',
            'admin_list.*' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'admin_list.required' => '目标不能为空',
        ];
    }

    public function passedValidation(): void
    {
        parent::passedValidation();
        $this->merge([
            'admin_list' => array_map(
                fn(string $item) => RequestTool::xxs($item),
                $this->input('admin_list')
            ),
        ]);
    }
}
