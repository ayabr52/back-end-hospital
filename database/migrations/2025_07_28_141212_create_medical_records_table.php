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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); // المريض صاحب السجل
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null'); // الطبيب الذي أضاف السجل
            $table->date('record_date'); // تاريخ السجل
            $table->string('diagnosis')->nullable(); // التشخيص
            $table->text('treatment')->nullable(); // العلاج الموصوف
            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
