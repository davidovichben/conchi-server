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
        Schema::create('program_report_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_report_question_id');
            $table->unsignedBigInteger('interaction_id')->nullable()->comment('Used for choosing an interaction for the next week');
            $table->string('content', 255);
            $table->timestamps();

            $table->foreign('interaction_id')->references('id')->on('interaction')->nullOnDelete();
            $table->foreign('program_report_question_id')->references('id')->on('program_report_questions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_report_options');
    }
};
