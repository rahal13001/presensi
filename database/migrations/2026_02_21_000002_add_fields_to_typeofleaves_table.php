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
        Schema::table('typeofleaves', function (Blueprint $table) {
            $table->boolean('has_quota')->default(false)->after('leaves_name');
            $table->integer('default_quota_days')->nullable()->after('has_quota');
            $table->boolean('requires_attachment')->default(false)->after('default_quota_days');
            $table->text('description')->nullable()->after('requires_attachment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('typeofleaves', function (Blueprint $table) {
            $table->dropColumn(['has_quota', 'default_quota_days', 'requires_attachment', 'description']);
        });
    }
};
