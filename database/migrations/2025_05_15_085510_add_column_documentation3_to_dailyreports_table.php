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
            if (!Schema::hasColumn('dailyreports', 'documentation3')) {
                $table->string('documentation3')->nullable();
            }
            if (!Schema::hasColumn('dailyreports', 'documentation4')) {
                $table->string('documentation4')->nullable();
            }
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
