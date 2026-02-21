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
        Schema::create('leave_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('typeofleave_id')->constrained('typeofleaves')->onDelete('cascade');
            $table->year('year');
            $table->integer('total_days');
            $table->integer('used_days')->default(0);
            $table->timestamps();
            
            // An employee can only have one quota record per leave type per year
            $table->unique(['user_id', 'typeofleave_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_quotas');
    }
};
