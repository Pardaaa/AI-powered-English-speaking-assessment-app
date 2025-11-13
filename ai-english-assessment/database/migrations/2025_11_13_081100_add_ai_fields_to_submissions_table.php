<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('audio_path_ai')->nullable()->after('file_path');
            $table->text('recognized_text_ai')->nullable()->after('audio_path_ai');

            $table->decimal('accuracy_score_ai', 5, 2)->nullable()->after('recognized_text_ai');
            $table->decimal('fluency_score_ai', 5, 2)->nullable()->after('accuracy_score_ai');
            $table->decimal('completeness_score_ai', 5, 2)->nullable()->after('fluency_score_ai');
            $table->decimal('pronunciation_score_ai', 5, 2)->nullable()->after('completeness_score_ai');

            $table->decimal('final_score_ai', 5, 2)->nullable()->after('pronunciation_score_ai');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn([
                'audio_path_ai',
                'recognized_text_ai',
                'accuracy_score_ai',
                'fluency_score_ai',
                'completeness_score_ai',
                'pronunciation_score_ai',
                'final_score_ai',
            ]);
        });
    }
};
