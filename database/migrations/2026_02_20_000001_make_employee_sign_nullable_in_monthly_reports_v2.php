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
            $table->text('employee_sign')->nullable()->change();
            $table->timestamp('employee_signed_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_reports_v2', function (Blueprint $table) {
            // Reverting to nullable is safer than forcing not null as data might be null now
            // But strict revert would be:
            // $table->text('employee_sign')->nullable(false)->change(); 
            // We kept them nullable in the original migration actually, but user request implies strictness elsewhere or just desire for clarity.
            // The original migration actually had ->nullable() already!
            // Line 21: $table->text('employee_sign')->nullable();
            // So this migration is strictly to ensure it, or valid if previous migration was edited.
            $table->text('employee_sign')->nullable()->change();
        });
    }
};
