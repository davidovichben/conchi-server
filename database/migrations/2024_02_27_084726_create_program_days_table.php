<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('week_id');
            $table->unsignedTinyInteger('number');
            $table->string('morning_label', 255)->default('בוקר טוב');
            $table->string('afternoon_label', 255)->default('אחה"צ/אחרי הגן');
            $table->string('evening_label', 255)->default('ערב');
            $table->string('night_label', 255)->default('לפני השינה');
            $table->timestamps();

            $table->unique(['week_id', 'number']);
            $table->foreign('week_id')->references('id')->on('program_weeks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_days');
    }
};
