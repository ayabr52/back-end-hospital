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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('processed_by_nurse_id')->nullable()->constrained('nurses')->onDelete('set null')->after('notes');
            $table->timestamp('processed_at')->nullable()->after('processed_by_nurse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['processed_by_nurse_id']);
            $table->dropColumn(['processed_by_nurse_id', 'processed_at']);
        });
    }
};

