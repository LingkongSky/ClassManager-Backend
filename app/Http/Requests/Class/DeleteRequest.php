<?php

namespace App\Http\Requests\Class;

use App\Utils\RequestTool;
use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $classList = $this->input('class_list');
        if (is_string($classList)) {
            $decoded = json_decode($classList, true);
            $this->merge(['class_list' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function rules(): array
    {
        return [
            'class_list' => 'required|array|min:1|max:500',
            'class_list.*' => 'required|string|max:20',
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
        parent::passedValidation();
        $this->merge([
            'class_list' => array_map(
                fn(string $item) => RequestTool::xxs($item),
                $this->input('class_list')
            ),
        ]);
    }
}
