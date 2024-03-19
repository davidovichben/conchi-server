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
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedTinyInteger('show_order')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('guidelines')->nullable();

            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('interaction_categories');
            $table->foreign('sub_category_id')->references('id')->on('interaction_sub_categories');
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
