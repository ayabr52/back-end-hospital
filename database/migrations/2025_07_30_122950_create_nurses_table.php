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
        Schema::create('nurses', function (Blueprint $table) {
            $table->id();
            // Link to the users table, ensuring each user has only one nurse profile
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('name'); // Nurse's name (can be redundant with user.name but useful for quick access)
            $table->string('phone')->nullable();
            $table->string('specialty')->nullable(); // e.g., Pediatric Nurse, ER Nurse
            $table->text('bio')->nullable(); // Short biography or description
            $table->string('image')->nullable(); // URL to nurse's profile image
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null'); // Link to department
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurses');
    }
};

