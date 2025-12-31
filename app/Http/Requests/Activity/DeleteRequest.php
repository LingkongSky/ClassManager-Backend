<?php

namespace App\Http\Requests\Activity;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{

    public function authorize()
    {
        return parent::authorizeActivityId($this->input('activity_id'));
    }

    public function rules(): array
    {
        return [
            'activity_id' => 'required|string|max:20',
        ];
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动ID不能为空',
        ];
    }
}
