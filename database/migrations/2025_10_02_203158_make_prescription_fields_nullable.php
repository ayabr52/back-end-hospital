<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePrescriptionFieldsNullable extends Migration
{
    public function up()
    {
        Schema::table('prescription_medicine', function (Blueprint $table) {
            $table->string('dosage')->nullable()->change();
            $table->string('frequency')->nullable()->change();
            $table->string('duration')->nullable()->change();
            $table->text('instructions')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('prescription_medicine', function (Blueprint $table) {
            $table->string('dosage')->nullable(false)->change();
            $table->string('frequency')->nullable(false)->change();
            $table->string('duration')->nullable(false)->change();
            $table->text('instructions')->nullable(false)->change();
        });
    }
}
