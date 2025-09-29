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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // عنوان المهمة
            $table->text('description')->nullable(); // وصف المهمة
            $table->foreignId('assigned_to_user_id')->constrained('users')->onDelete('cascade'); // المستخدم المكلف بالمهمة
            $table->foreignId('assigned_by_user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي قام بتكليف المهمة
            $table->string('status')->default('pending'); // حالة المهمة: pending, in_progress, completed, cancelled
            $table->string('priority')->default('medium'); // أولوية المهمة: low, medium, high
            $table->dateTime('due_date')->nullable(); // تاريخ استحقاق المهمة
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null'); // المريض المرتبط بالمهمة (اختياري)
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null'); // القسم المرتبط بالمهمة (اختياري)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
