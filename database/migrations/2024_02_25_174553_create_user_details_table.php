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
        Schema::create('user_details', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->enum('family_status', ['married', 'divorced', 'widowed', 'sin   gle'])->nullable();
            $table->string('parent1_name', 50)->nullable();
            $table->enum('parent1_role', ['father', 'mother'])->nullable();
            $table->string('parent2_name', 50)->nullable();
            $table->enum('parent2_role', ['father', 'mother'])->nullable();
            $table->enum('child_gender', ['male', 'female'])->nullable();
            $table->date('child_birth_date')->nullable();
            $table->string('child_name', 50)->nullable();
            $table->boolean('child_has_nickname')->default(false);
            $table->string('child_nickname', 50)->nullable();
            $table->enum('child_position', ['middle', 'single', 'youngest', 'eldest'])->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
