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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('payment_package_id')->nullable();
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->char('mobile', 10)->unique()->nullable();
            $table->string('email', 150)->unique();
            $table->char('password', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('payment_package_id')->references('id')->on('payment_packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
