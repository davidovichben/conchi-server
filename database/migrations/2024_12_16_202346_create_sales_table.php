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
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); // auto-incrementing ID for the sales record
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_package_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->date('date'); // the date of the sale
            $table->timestamps(); // automatically adds created_at and updated_at columns
        
            $table->foreign(columns: 'user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign(columns: 'payment_package_id')->references('id')->on('payment_packages')->cascadeOnDelete();
            $table->foreign(columns: 'coupon_id')->references('id')->on('coupons')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
