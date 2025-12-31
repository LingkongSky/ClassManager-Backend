<?php

namespace App\Http\Requests\Class;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules(): array
    {
        return [
            'class_list' => 'required|string|max:9999',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'class_list.required' => '班级不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        $class_list = json_decode($input["class_list"], true);

        if ($class_list === null && json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        if (!is_array($class_list)) {
            return;
        }

        // 遍历并处理每个元素
        foreach ($class_list as &$item) {
            $class_id = RequestTool::xxs($item);
            $item = $class_id;
        }

        $input["class_list"] = $class_list;
        $this->replace($input);
    }
}
