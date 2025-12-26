<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('account', 20)->nullable(false);
            $table->string('activity_id', 20)->nullable(false);
            $table->string('time', 99)->nullable(false);
            $table->string('event', 99)->nullable(false);
            $table->string('ip', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};