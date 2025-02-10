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
        Schema::table('monthlyreports', function (Blueprint $table) {
            $table->string('dukman_leader')->nullable();
            $table->string('dukman_idnumber')->nullable();
            $table->text('dukman_sign')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthlyreport', function (Blueprint $table) {
            //
        });
    }
};
