<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Request;
use App\Utils\RequestTool;

class EditAdminRequest extends Request
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|max:20',
            'name' => 'required|string|max:10',
            'class_id' => 'present|nullable|max:20',
            'class_ids' => 'sometimes|string|max:4096',
            'primary_class_id' => 'nullable|string|max:20',
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

    public function passedValidation(): void
    {
        $input = $this->all();

        $classIds = [];
        if (isset($input['class_ids']) && is_string($input['class_ids'])) {
            $decoded = json_decode($input['class_ids'], true);
            if (is_array($decoded)) {
                $classIds = $decoded;
            }
        }

        $classIds = array_values(array_unique(array_filter(array_map(
            fn($item) => is_string($item) ? RequestTool::xxs($item) : '',
            $classIds
        ))));

        if (empty($classIds) && !empty($input['class_id'])) {
            $classIds = [RequestTool::xxs($input['class_id'])];
        }

        $primaryClassId = RequestTool::xxs($input['primary_class_id'] ?? ($classIds[0] ?? ''));
        if (!empty($primaryClassId) && !in_array($primaryClassId, $classIds, true)) {
            $classIds[] = $primaryClassId;
        }

        $input['class_ids'] = $classIds;
        $input['primary_class_id'] = $primaryClassId;
        $this->replace($input);
    }
}
