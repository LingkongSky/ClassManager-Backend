<?php

namespace App\Http\Requests\Activity;

use App\Utils\RequestTool;
use App\Http\Requests\Request;
use App\Models\Activity;

class ExportRequest extends Request
{

    public function authorize()
    {
        return parent::authorizeActivityId($this->input('activity_id'));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
            'times' => 'required|string|max:10',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
            'times.required' => '标记不能为空',
        ];
    }
}
