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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); // ربط المريض بحسابه
            $table->string('name'); // اسم المريض (يمكن أن يكون مكرراً مع اسم المستخدم ولكن يفضل فصله)
            $table->string('phone')->nullable();
            $table->string('national_id')->unique()->nullable();
            $table->string('address')->nullable();
            $table->date('dob')->nullable(); // Date of Birth
            $table->string('gender')->nullable(); // male, female
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
