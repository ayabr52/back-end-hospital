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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); // ربط الطبيب بحسابه
            $table->string('name'); // اسم الطبيب (يمكن أن يكون مكرراً مع اسم المستخدم ولكن يفضل فصله)
            $table->string('specialty'); // تخصص الطبيب (مثال: جراحة، داخلية، جلدية)
            $table->text('bio')->nullable(); 
            $table->string('image')->nullable(); 
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null'); // ربط الطبيب بالقسم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
