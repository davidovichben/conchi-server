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
        Schema::create('user_sentences', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sentence_id');

            $table->timestamps();

            $table->primary(['user_id', 'sentence_id']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('sentence_id')->references('id')->on('interactions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sentences');
    }
};
