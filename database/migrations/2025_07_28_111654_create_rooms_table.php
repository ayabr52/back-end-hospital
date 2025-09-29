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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique(); // رقم الغرفة (مثال: 101, ICU-1)
            $table->string('type'); // نوع الغرفة (مثال: private, semi-private, ward, ICU, OR)
            $table->integer('capacity'); // سعة الغرفة (عدد الأسرة)
            $table->string('status')->default('available'); // حالة الغرفة: available, occupied, maintenance, cleaning
            $table->text('notes')->nullable(); // ملاحظات حول الغرفة
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null'); // القسم الذي تنتمي إليه الغرفة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
