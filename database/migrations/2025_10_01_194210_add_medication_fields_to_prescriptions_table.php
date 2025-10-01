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
        $table->string('medication')->nullable()->after('medical_record_id');
        $table->string('dosage')->nullable()->after('medication');
        $table->text('instructions')->nullable()->after('dosage');
    });
}

public function down()
{
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->dropColumn(['medication', 'dosage', 'instructions']);
    });
}

};
