<?php

namespace App\Http\Controllers\Class;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\GetRequest;
use App\Http\Requests\Class\GetCountRequest;
use App\Http\Requests\Class\GetClassIdRequest;
use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Utils\ResponseTool;

class ClassListController extends Controller
{
    // 查询班级
    public function get(GetRequest $request)
    {
        $queryCount = $request->input('query_count');
        $queryWord = $request->input('query_word');
        $sortMode = $request->input('sort_mode');
        $sortField = $request->input('sort_field');

        $allClass = [];

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
            array_push($allClass, $classData);
        }

        return ResponseTool::jsonResponse(0, 'success', $allClass);
    }

    // 获取班级列表
    public function getList(Request $request)
    {
        return ResponseTool::jsonResponse(0, 'success', $request->user()->getStudentPermissionClass());
    }

    // 获取班级数量
    public function getCount(GetCountRequest $request)
    {
        return ResponseTool::jsonResponse(0, 'success', ClassModel::getClassCount($request->input("query_word")));
    }

    // 根据班级名称获取班级ID
    public function getClassId(GetClassIdRequest $request)
    {
        $className = $request->input('class_name');
        $classId = ClassModel::where('class_name', $className)->value('class_id');

        if (is_null($classId)) {
            return ResponseTool::jsonResponse(1, 'class not found', null, 404);
        }

        return ResponseTool::jsonResponse(0, 'success', [
            'class_id' => $classId,
        ]);
    }
}
