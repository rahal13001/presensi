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
        Schema::table('monthly_reports_v2', function (Blueprint $table) {
            $table->string('city')->nullable()->default('Sorong')->after('team_leader_name');
            $table->date('signed_date')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_reports_v2', function (Blueprint $table) {
            $table->dropColumn(['city', 'signed_date']);
        });
    }
};
