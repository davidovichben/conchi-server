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
        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interaction_id', 100);
            $table->string('file', 255);
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('parents_status', ['couple', 'single_male', 'single_female'])->nullable();
            $table->unsignedSmallInteger('duration')->nullable();
            $table->timestamps();

            $table->foreign('interaction_id')->references('id')->on('interactions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
