<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('student', 'class_id')) {
            if (!Schema::hasTable('student_class_map')) {
                throw new RuntimeException('student_class_map 不存在，禁止删除 student.class_id 以避免数据丢失。');
            }

            // 仅检查旧字段不为空的学生，确保映射都已存在
            $missingMappings = DB::table('student as s')
                ->leftJoin('student_class_map as scm', function ($join) {
                    $join->on('scm.student_id', '=', 's.id')
                        ->on('scm.class_id', '=', 's.class_id');
                })
                ->whereNotNull('s.class_id')
                ->where('s.class_id', '<>', '')
                ->whereNull('scm.id')
                ->count();

            if ($missingMappings > 0) {
                throw new RuntimeException("存在 {$missingMappings} 条学生班级未完成映射，禁止删除 student.class_id。");
            }

            Schema::table('student', function (Blueprint $table) {
                $table->dropColumn('class_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->string('class_id', 20)->nullable();
            $table->index('class_id');
            $table->index(['class_id', 'level']);
            $table->index(['class_id', 'date']);
        });
    }
};
