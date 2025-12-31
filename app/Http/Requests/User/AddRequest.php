<?php

namespace App\Http\Requests\User;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class AddRequest extends Request
{
    public function rules(): array
    {
        return [
            'data' => 'required|string|max:409600',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'data.required' => '对象不能为空',
        ];
    }

    public function passedValidation(): void
    {
        $input = $this->all();

        $data = json_decode($input["data"], true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        if (!is_array($data)) {
            return;
        }

        // 遍历并处理每个元素
        foreach ($data as &$item) {
            if (isset($item["name"]) && isset($item["account"]) && isset($item["password"]) && isset($item["class_id"])) {
                $item["name"] =  RequestTool::xxs($item["name"]);
                $item["account"] =  RequestTool::xxs($item["account"]);
                $item["password"] =  RequestTool::xxs($item["password"]);
                $item["class_id"] =  RequestTool::xxs($item["class_id"]);
            }
        }

        $input["data"] = $data;
        $this->replace($input);
    }
}
