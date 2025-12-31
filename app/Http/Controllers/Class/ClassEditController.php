<?php

namespace App\Http\Controllers\Class;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\AddRequest;
use App\Http\Requests\Class\DeleteRequest;
use App\Http\Requests\Class\EditRequest;
use App\Models\ClassModel;
use App\Utils\ResponseTool;

class ClassEditController extends Controller
{
    // 创建班级
    public function add(AddRequest $request)
    {
        $classList = $request->input("class_list");
        $i = 0;

        foreach ($classList as $item) {
            $time = time() . $i;
            ClassModel::addClass($time, $item["class_name"]);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除班级
    public function delete(DeleteRequest $request)
    {
        $classList = $request->input("class_list");

        foreach ($classList as $item) {
            ClassModel::deleteClass($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 编辑班级
    public function edit(EditRequest $request)
    {
        $classId = $request->input('class_id');
        $className = $request->input('class_name');
        ClassModel::editClass($classId, $className);
        return ResponseTool::jsonResponse(0, 'success', null);
    }

}
