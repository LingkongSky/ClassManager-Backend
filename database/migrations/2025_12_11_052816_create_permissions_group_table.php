<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions_group', function (Blueprint $table) {
            $table->id();
            $table->string('group_id', 20)->unique()->nullable(false);
            $table->string('name', 40)->nullable(false);
            $table->string('target', 9999)->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions_group');
    }
};