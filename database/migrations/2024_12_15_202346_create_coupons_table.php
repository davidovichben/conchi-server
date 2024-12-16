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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Coupon code
            $table->integer('discount'); // Discount value as integer
            $table->date('start_date')->nullable(); // Start date for coupon validity
            $table->date('end_date')->nullable(); // End date for coupon validity
            $table->boolean('is_active')->default(false); // Whether the coupon is active
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
