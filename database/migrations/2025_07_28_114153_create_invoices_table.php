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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // الموظف الذي أصدر الفاتورة
            $table->string('invoice_number')->unique(); // رقم الفاتورة الفريد
            $table->decimal('total_amount', 10, 2); // إجمالي المبلغ
            $table->decimal('paid_amount', 10, 2)->default(0.00); // المبلغ المدفوع
            $table->string('status')->default('pending'); // حالة الفاتورة: pending, paid, partially_paid, cancelled
            $table->text('description')->nullable(); // وصف مختصر للفاتورة (مثال: رسوم استشارة، علاج)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};