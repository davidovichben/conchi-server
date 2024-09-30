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
        Schema::create('interaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image', 80)->nullable();
            $table->enum('role', ['hobbies', 'power_sentences', 'option_sentences', 'general_sentences'])->nullable();
            $table->enum('should_display', ['interactions', 'sub_categories'])->default('interactions');
            $table->boolean('is_personalized')->default(false);
            $table->unsignedTinyInteger('personalization_limit')->nullable();
            $table->text('title_1')->nullable();
            $table->text('title_2')->nullable();
            $table->text('title_3')->nullable();
            $table->text('title_4')->nullable();

            $table->timestamps();

            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_category');
    }
};
