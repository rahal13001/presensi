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
            $table->string('team_name')->nullable()->after('year');
            $table->string('team_leader_name')->nullable()->after('team_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_reports_v2', function (Blueprint $table) {
            $table->dropColumn(['team_name', 'team_leader_name']);
        });
    }
};
