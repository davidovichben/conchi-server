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
        Schema::create('program_report_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_week_id');
            $table->string('content', 255);
            $table->timestamps();

            $table->foreign('program_week_id')->references('id')->on('program_weeks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_report_questions');
    }
};
