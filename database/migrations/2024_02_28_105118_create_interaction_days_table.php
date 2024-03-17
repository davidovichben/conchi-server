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
        Schema::create('interaction_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interaction_id');
            $table->unsignedBigInteger('day_id');
            $table->enum('period', ['morning', 'afternoon', 'evening', 'night']);
            $table->timestamps();

            $table->foreign('interaction_id')->references('id')->on('interactions')->cascadeOnDelete();
            $table->foreign('day_id')->references('id')->on('program_days')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_days');
    }
};
