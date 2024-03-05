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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->enum('language', ['he', 'en'])->default('he');
            $table->enum('related_to', ['general', 'sentences', 'categories'])->default('general');
            $table->string('name', 120);
            $table->text('value');
            $table->timestamps();
            //$table->unique(['language', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
