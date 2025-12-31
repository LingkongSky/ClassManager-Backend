<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Utils\RequestTool;

class SubmitRequest extends Request
{
    public function authorize()
    {
        return parent::authorizeActivityInClass($this->input('activity_id'));
    }

    public function rules(): array
    {
        $rules = [
            'activity_id' => 'required|string|max:20',
            'file' => 'required|file|max:204800',
        ];

        $activityId = RequestTool::xxs($this->input('activity_id'));
        $acceptTypes = str_replace('.', '', Activity::getAccept($activityId));
        $rules['file'] .= '|mimes:' . $acceptTypes . ',' . strtoupper($acceptTypes);

        return $rules;
    }

    public  function specificMessages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
            'file.required' => '文件不能为空',
            'file.mimes' => '文件类型不支持，请上传指定格式的文件',
        ];
    }
}
