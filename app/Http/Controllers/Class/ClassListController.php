<?php

namespace App\Http\Controllers\Class;

use App\Http\Controllers\Controller;
use App\Http\Requests\Class\GetRequest;
use App\Http\Requests\Class\GetCountRequest;
use App\Http\Requests\Class\GetClassIdRequest;
use App\Http\Requests\Class\GetStudentsByClassesRequest;
use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
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

    // 兼容旧接口：POST class/list
    public function list(Request $request)
    {
        $queryCount = (int)$request->input('query_count', 0);
        $queryWord = $request->input('query_word', '');

        $classes = ClassModel::queryClasses($queryCount, $queryWord, 200);
        return ResponseTool::jsonResponse(0, 'success', $classes);
    }

    // 根据多个来源班级获取学生列表（去重）
    public function getStudentsByClasses(GetStudentsByClassesRequest $request)
    {
        $sourceClassIds = $request->input('source_class_ids');
        $queryWord = $request->input('query_word');

        if (empty($sourceClassIds)) {
            return ResponseTool::jsonResponse(0, 'success', []);
        }

        $rows = DB::table('student')
            ->join('student_class_map as scm', 'scm.student_id', '=', 'student.id')
            ->whereIn('scm.class_id', $sourceClassIds)
            ->when(!empty($queryWord), fn($query) => $query->where('student.name', 'like', $queryWord . '%'))
            ->select('student.account', 'student.name')
            ->distinct()
            ->orderBy('student.account')
            ->limit(5000)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $classIds = Student::getUserClasses($row->account);
            $primaryClassId = $classIds[0] ?? null;
            $classNames = array_map(fn($id) => ClassModel::getClassName($id), $classIds);
            $result[] = [
                'account' => $row->account,
                'name' => $row->name,
                'class_ids' => $classIds,
                'class_names' => $classNames,
                'primary_class_id' => $primaryClassId,
                'primary_class_name' => $primaryClassId ? ClassModel::getClassName($primaryClassId) : null,
            ];
        }

        return ResponseTool::jsonResponse(0, 'success', $result);
    }
}
