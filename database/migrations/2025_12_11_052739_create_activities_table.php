<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id', 20)->unique()->nullable(false);
            $table->string('name', 20)->nullable(false);
            $table->string('title', 20)->nullable(false);
            $table->string('start_time', 99)->nullable(false);
            $table->string('stop_time', 99)->nullable(false);
            $table->string('class_id', 20)->nullable();
            $table->string('rename_format', 99)->nullable();
            $table->string('requestment', 999)->nullable();
            $table->string('accept', 99)->nullable();
            $table->string('status', 5)->nullable(false);
            $table->string('type', 5)->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};