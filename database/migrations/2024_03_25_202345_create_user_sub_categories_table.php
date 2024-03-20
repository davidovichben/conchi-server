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
        Schema::create('user_sub_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sub_category_id');

            $table->timestamps();

            $table->primary(['user_id', 'sub_category_id']);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('sub_category_id')->references('id')->on('interaction_sub_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sub_categories');
    }
};
