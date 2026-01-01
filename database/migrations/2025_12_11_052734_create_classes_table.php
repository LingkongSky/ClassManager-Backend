<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class', function (Blueprint $table) {
            $table->id();
            $table->string('class_id', 20)->unique()->nullable(false);
            $table->string('class_name', 20)->nullable(false);
            $table->string('student_count', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class');
    }
};