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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); // المريض الذي أجريت له التحاليل
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null'); // الطبيب الذي طلب التحاليل
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // موظف المختبر/المستخدم الذي أدخل النتائج
            $table->string('test_type'); // نوع التحليل (مثال: Blood Count, Urine Analysis, X-ray)
            $table->date('test_date'); // تاريخ إجراء التحليل
            $table->string('result_status')->default('pending'); // حالة النتيجة: pending, completed, reviewed
            $table->text('results')->nullable(); // نتائج التحليل (يمكن أن تكون JSON لنتائج معقدة)
            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};