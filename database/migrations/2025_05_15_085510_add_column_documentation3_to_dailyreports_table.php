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
        Schema::table('dailyreports', function (Blueprint $table) {
            $table->string('documentation3')->nullable(); // Add the new column with a nullable constrain
            $table->string('documentation4')->nullable(); // Add the new column with a nullable constrain
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dailyreports', function (Blueprint $table) {
            $table->dropColumn('documentation3'); // Remove the new column
            $table->dropColumn('documentation4'); // Remove the new column
        });
    }
};
