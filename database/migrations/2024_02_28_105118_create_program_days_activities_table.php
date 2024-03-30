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
        Schema::create('program_days_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_day_activity_id')->index();
            $table->unsignedBigInteger('program_day_id');
            $table->enum('program_day_activity_type', ['App\Models\Interaction', 'App\Models\InteractionCategory']);
            $table->enum('period', ['morning', 'afternoon', 'evening', 'night']);
            $table->timestamps();

            $table->unique(['program_day_id', 'period']);
            $table->foreign('program_day_id')->references('id')->on('program_days')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_days_activities');
    }
};
