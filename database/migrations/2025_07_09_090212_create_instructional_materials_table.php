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
        Schema::create('instructional_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_type');
            $table->string('file_path');
            $table->boolean('isOffline')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructional_materials');
    }
};
