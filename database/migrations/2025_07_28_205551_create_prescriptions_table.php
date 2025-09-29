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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); // المريض صاحب الوصفة
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null'); // الطبيب الذي كتب الوصفة
            $table->date('prescription_date'); // تاريخ كتابة الوصفة
            $table->text('notes')->nullable(); // ملاحظات عامة للوصفة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
