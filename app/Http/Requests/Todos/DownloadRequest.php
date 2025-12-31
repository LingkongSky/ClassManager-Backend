<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;

class DownloadRequest extends Request
{
    public function authorize()
    {
        return parent::authorizeActivityInClass($this->input('activity_id'));
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
            'activity_id.required' => '活动名不能为空',
        ];
    }
}
