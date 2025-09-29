<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('inventories', function (Blueprint $table) {
        $table->id();
        $table->string('item_name'); // اسم الصنف
        $table->integer('quantity'); // الكمية
        $table->text('notes')->nullable(); // الملاحظات، nullable يعني أنه يمكن أن يكون فارغًا
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
