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
        Schema::table('postcodes', function (Blueprint $table) {
            // Index for postcode to increase query performance
            $table->index('postcode');

            // Index for latitude and longitude to increase query performance
            $table->index('latitude');
            $table->index('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postcodes', function (Blueprint $table) {
            $table->dropIndex('postcode');
            $table->dropIndex('latitude');
            $table->dropIndex('longitude');
        });
    }
};
