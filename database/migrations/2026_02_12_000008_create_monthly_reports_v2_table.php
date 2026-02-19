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
        Schema::create('monthly_reports_v2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->year('year');

            // Employee signature
            $table->text('employee_sign')->nullable();
            $table->timestamp('employee_signed_at')->nullable();

            // Team leader review
            $table->foreignId('team_leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('condition_status', ['baik', 'rusak', 'permasalahan'])->nullable();
            $table->text('leader_notes')->nullable();
            $table->text('leader_sign')->nullable();
            $table->timestamp('leader_signed_at')->nullable();

            // Workflow status
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');

            $table->timestamps();
            $table->unique(['user_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_reports_v2');
    }
};
