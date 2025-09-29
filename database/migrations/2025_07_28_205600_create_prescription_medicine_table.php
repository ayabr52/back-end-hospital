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
        Schema::create('prescription_medicine', function (Blueprint $table) {
            $table->id(); // يمكن استخدام معرف فريد لكل سطر في الجدول الوسيط
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->string('dosage'); // الجرعة (مثال: 500mg, 1 tablet)
            $table->string('frequency'); // التكرار (مثال: Twice daily, Every 8 hours)
            $table->string('duration')->nullable(); // المدة (مثال: 7 days, Until finished)
            $table->text('instructions')->nullable(); // تعليمات خاصة (مثال: Take with food)
            $table->timestamps(); // اختياري للجدول الوسيط، لكن مفيد للتتبع
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_medicine');
    }
};