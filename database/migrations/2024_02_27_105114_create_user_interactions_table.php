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
            $table->enum('status', ['initial', 'started', 'completed'])->default('initial');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('interaction_id')->references('id')->on('interactions');
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
