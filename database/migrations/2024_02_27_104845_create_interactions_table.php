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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedTinyInteger('duration')->comment('In minutes')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('guidelines')->nullable();
            $table->string('audio', 80);

            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('interaction_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction');
    }
};
