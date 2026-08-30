<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('student')
            || !Schema::hasTable('student_class_map')
            || !Schema::hasColumn('student', 'class_id')
        ) {
            return;
        }

        $studentClassId = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)
            ? DB::raw('s.class_id COLLATE utf8mb4_general_ci')
            : 's.class_id';

        // Preserve legacy assignments even when the referenced class has already
        // been removed. This lets the following migration drop the old column
        // without silently discarding the original relationship.
        DB::table('student_class_map')->insertUsing(
            ['student_id', 'class_id', 'is_primary', 'created_at'],
            DB::table('student as s')
                ->leftJoin('student_class_map as scm', function ($join) use ($studentClassId) {
                    $join->on('scm.student_id', '=', 's.id')
                        ->on('scm.class_id', '=', $studentClassId);
                })
                ->whereNotNull('s.class_id')
                ->where('s.class_id', '<>', '')
                ->whereNull('scm.id')
                ->select([
                    's.id',
                    's.class_id',
                    DB::raw('1'),
                    DB::raw('CURRENT_TIMESTAMP'),
                ])
        );
    }

    public function down(): void
    {
        // Preserved legacy relationships cannot be distinguished safely from
        // later legitimate mappings, so rollback intentionally leaves them.
    }
};
