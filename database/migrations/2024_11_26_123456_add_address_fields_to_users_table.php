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
        Schema::table('users', function (Blueprint $table) {
            $table->string('street')->nullable()->after('city_id');  // Street name
            $table->integer('number')->nullable()->after('street'); // House/building number
            $table->integer('apartment')->nullable()->after('number'); // Apartment number
            $table->integer('floor')->nullable()->after('apartment'); // Floor number
            $table->string('zip_code', 10)->nullable()->after('floor'); // Postal code (up to 10 characters)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['street', 'number', 'apartment', 'floor', 'zip_code']);
        });
    }
};
