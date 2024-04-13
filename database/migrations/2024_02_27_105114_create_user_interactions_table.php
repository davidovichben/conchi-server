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
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('interaction_id');
            $table->boolean('liked')->default(0);
            $table->boolean('selected')->default(0);
            $table->enum('status', ['initial', 'started', 'completed'])->default('initial');
            $table->timestamps();

            $table->unique(['user_id', 'interaction_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('interaction_id')->references('id')->on('interactions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_interaction');
    }
};
