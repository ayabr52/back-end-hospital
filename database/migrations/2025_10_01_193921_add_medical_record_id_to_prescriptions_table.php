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
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->unsignedBigInteger('medical_record_id')->nullable()->after('doctor_id');

        $table->foreign('medical_record_id')
              ->references('id')->on('medical_records')
              ->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->dropForeign(['medical_record_id']);
        $table->dropColumn('medical_record_id');
    });
}

};
