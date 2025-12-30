<?php

namespace App\Http\Requests\Todos;

use App\Http\Requests\Request;
use App\Models\Activity;
use App\Utils\RequestTool;

class SubmitRequest extends Request
{

    public function authorize()
    {
        $activityId = RequestTool::xxs($this->input('activity_id'));
        return ($this->user()->class_id == Activity::getClass($activityId));
    }

    public function rules(): array
    {
        $rules = [
            'activity_id' => 'required|string|max:100',
            'file' => 'required|file|max:204800',
        ];
        
        $activityId = RequestTool::xxs($this->input('activity_id'));
        $acceptTypes = str_replace('.','',Activity::getAccept($activityId));
        $rules['file'] .= '|mimes:' . $acceptTypes;     
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => '活动名不能为空',
            'file.required' => '文件不能为空',
            'file.mimes' => '文件类型不支持，请上传指定格式的文件',
        ];
    }
}