<?php

namespace App\Http\Requests\User;

use App\Utils\ResponseTool;
use App\Utils\RequestTool;
use App\Http\Requests\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

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

        // 兼容单对象提交：统一包装成数组
        if (isset($data['account']) && isset($data['name'])) {
            $data = [$data];
        }

        // 遍历并处理每个元素
        foreach ($data as &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item["name"]) && isset($item["account"]) && isset($item["password"]) && isset($item["class_ids"])) {
                $item["name"] =  RequestTool::xxs($item["name"]);
                $item["account"] =  RequestTool::xxs($item["account"]);
                $item["password"] =  RequestTool::xxs($item["password"]);
                $item["class_ids"] = array_map(fn($classId) => RequestTool::xxs($classId), $item["class_ids"]);
                $item["primary_class_id"] = RequestTool::xxs($item["primary_class_id"] ?? ($item["class_ids"][0] ?? ''));

                // 仅一个班级时，主班级必须与该班级一致
                if (count($item["class_ids"]) === 1 && $item["primary_class_id"] !== $item["class_ids"][0]) {
                    throw new HttpResponseException(
                        ResponseTool::jsonResponse(
                            1,
                            '仅有一个班级时，主班级必须与班级一致',
                            ['errors' => ['primary_class_id' => ['仅有一个班级时，主班级必须与班级一致']]],
                            422
                        )
                    );
                }
            }
        }

        $input["data"] = $data;
        $this->replace($input);
    }
}
