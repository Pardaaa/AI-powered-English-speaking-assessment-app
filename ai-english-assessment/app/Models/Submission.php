<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'assignment_id',
    'file_path',
    'original_filename',
    'notes',
    'status',

    'audio_path_ai',
    'recognized_text_ai',
    'accuracy_score_ai',
    'fluency_score_ai',
    'completeness_score_ai',
    'pronunciation_score_ai',
    'final_score_ai',

    'score_dosen',    
    'feedback_dosen',  
];

protected $casts = [
    'accuracy_score_ai'      => 'float',
    'fluency_score_ai'       => 'float',
    'completeness_score_ai'  => 'float',
    'pronunciation_score_ai' => 'float',
    'final_score_ai'         => 'float',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
