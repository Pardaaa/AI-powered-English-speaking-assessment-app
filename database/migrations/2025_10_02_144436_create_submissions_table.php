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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('pending');

            $table->text('transcript_ai')->nullable(); 
            $table->float('score_pronunciation_ai')->nullable(); 
            $table->float('score_fluency_ai')->nullable(); 
            $table->json('mispronounced_words_ai')->nullable(); 
            $table->text('vocabulary_report_ai')->nullable(); 

            $table->float('score_dosen')->nullable(); 
            $table->text('feedback_dosen')->nullable(); 

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
