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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke tabel users (Dosen)
            $table->string('name'); //  "Teknik Komunikasi Bahasa Inggris"
            $table->string('code')->unique(); // "IN200"
            $table->string('semester'); // "Ganjil 2025/2026"
            $table->text('description')->nullable(); // Deskripsi
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
