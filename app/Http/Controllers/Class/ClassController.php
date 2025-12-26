<?php

namespace App\Http\Controllers\Class;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\AddClassRequest;
use App\Http\Requests\Class\DeleteClassRequest;
use App\Http\Requests\Class\EditClassRequest;
use App\Http\Requests\Class\GetClassRequest;
use App\Http\Requests\Class\GetCountClassRequest;
use App\Models\ClassModel;
use App\Utils\ResponseTool;

class ClassController extends Controller
{
    // 创建班级
    public function add(AddClassRequest $request)
    {
        $class_list = $request->input("class_list");
        $i = 0;

        foreach ($class_list as $item) {
            $time = time() . $i;
            ClassModel::addClass($time, $item["class_name"]);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 删除班级
    public function delete(DeleteClassRequest $request)
    {
        $class_list = $request->input("class_list");

        foreach ($class_list as $item) {
            ClassModel::deleteClass($item);
        }

        return ResponseTool::jsonResponse(0, 'success', null);
    }

    // 查询班级
    public function get(GetClassRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $sortMode = $request->input('sort_mode');
        $sortField = $request->input('sort_field');

        $all_class = [];

        if (!is_null($sortMode) && !is_null($sortField)) {
            $classes = ClassModel::queryClassesWithSort($queryCount, $queryWord, $sortMode, $sortField);
        } else {
            $classes = ClassModel::queryClasses($queryCount, $queryWord);
        }

        // 构建返回格式
        foreach ($classes as $class) {
            $classData = array(
                'class_id' => $class->class_id,
                'class_name' => $class->class_name,
                'student_count' => $class->student_count,
            );
            array_push($all_class, $classData);
        }

        return ResponseTool::jsonResponse(0, 'success', $all_class);
    }

    public function edit(EditClassRequest $request)
    {
        $classId = $request->input('class_id');
        $className = $request->input('class_name');
        ClassModel::editClass($classId, $className);
        return ResponseTool::jsonResponse(0, 'success', null);
    }


    // 获取班级数量
    public function getCount(GetCountClassRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', ClassModel::getClassCount($request->input("query_word")));
    }
}
