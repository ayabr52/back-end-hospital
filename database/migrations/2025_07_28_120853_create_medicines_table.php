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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم الدواء
            $table->string('generic_name')->nullable(); // الاسم العلمي للدواء
            $table->string('manufacturer')->nullable(); // الشركة المصنعة
            $table->string('dosage_form')->nullable(); // شكل الجرعة (أقراص، شراب، حقن)
            $table->string('strength')->nullable(); // قوة الدواء (مثال: 500mg, 10ml)
            $table->integer('stock_quantity')->default(0); // الكمية المتوفرة في المخزون
            $table->decimal('price', 8, 2)->default(0.00); // سعر الوحدة
            $table->date('expiry_date')->nullable(); // تاريخ انتهاء الصلاحية
            $table->text('description')->nullable(); // وصف أو استخدامات الدواء
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};