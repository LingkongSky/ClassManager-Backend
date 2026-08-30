<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true) || !Schema::hasTable('student_class_map')) {
            return;
        }

        DB::statement("
            ALTER TABLE student_class_map
            MODIFY class_id VARCHAR(20)
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_general_ci
            NOT NULL
        ");
    }

    public function down(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true) || !Schema::hasTable('student_class_map')) {
            return;
        }

        DB::statement("
            ALTER TABLE student_class_map
            MODIFY class_id VARCHAR(20)
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_unicode_ci
            NOT NULL
        ");
    }
};
