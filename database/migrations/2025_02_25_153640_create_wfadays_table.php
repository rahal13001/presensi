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
        if (!Schema::hasTable('wfadays')) {
            Schema::create('wfadays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('schedule_id')->constrained()->OnDelete('cascade');
                $table->string('day_name');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfadays');
    }
};
