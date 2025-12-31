<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;
use App\Utils\RequestTool;
use App\Models\Activity;

class GetListRequest extends Request
{

    public function authorize()
    {
        return parent::authorizeClassId($this->input('class_id'));
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'class_id.required' => '班级ID不能为空',
        ];
    }
}
