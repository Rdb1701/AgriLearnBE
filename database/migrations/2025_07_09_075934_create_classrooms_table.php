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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete(); //instructor user per classroom or section
            $table->string('class_name');
            $table->string('subject');
            $table->string('section_code');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
