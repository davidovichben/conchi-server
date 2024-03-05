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
        Schema::create('user_program_weeks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('program_week_id');
            $table->text('review')->nullable();
            $table->boolean('completed')->default(0);
            $table->enum('status', ['locked', 'completed', 'active']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('program_week_id')->references('id')->on('program_weeks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_program_weeks');
    }
};
