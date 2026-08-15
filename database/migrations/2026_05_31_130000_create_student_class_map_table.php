<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_class_map')) {
            return;
        }

        Schema::create('student_class_map', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->string('class_id', 20)->collation('utf8mb4_general_ci');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->unique(['student_id', 'class_id'], 'uk_student_class');
            $table->index(['class_id', 'student_id'], 'idx_class_student');
            $table->index(['student_id', 'is_primary'], 'idx_student_primary');
        });

        // 数据转换：把旧 student.class_id 回填为映射表主班级
        DB::transaction(function () {
            // 仅回填存在于 class 表中的合法班级，避免脏映射写入
            DB::statement("
                INSERT INTO student_class_map (student_id, class_id, is_primary, created_at)
                SELECT s.id, s.class_id, 1, NOW()
                FROM student s
                INNER JOIN class c ON c.class_id = s.class_id
                WHERE s.class_id IS NOT NULL AND s.class_id <> ''
            ");
        });
    }

    public function down(): void
    {
        // 若已回滚恢复了 student.class_id，则尽量回填主班级，避免信息丢失
        if (Schema::hasTable('student_class_map') && Schema::hasColumn('student', 'class_id')) {
            DB::statement("
                UPDATE student s
                LEFT JOIN (
                    SELECT scm.student_id, scm.class_id
                    FROM student_class_map scm
                    INNER JOIN (
                        SELECT student_id, MIN(id) AS keep_id
                        FROM student_class_map
                        WHERE is_primary = 1
                        GROUP BY student_id
                    ) p ON scm.id = p.keep_id
                ) m ON s.id = m.student_id
                SET s.class_id = m.class_id
            ");
        }

        Schema::dropIfExists('student_class_map');
    }
};
