<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'quiz_id',
        'question',
        'type',
        'options',
        'correct_answer',
        'points',
        'ordre',
        'explication',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'float',
    ];

    // Relations
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    // Helper methods
    public function isCorrect($userAnswer)
    {
        if (is_null($userAnswer)) return false;

        switch ($this->type) {
            case 'multiple':
            case 'unique':
                return $userAnswer === $this->correct_answer;
            
            case 'texte':
                return strtolower(trim($userAnswer)) === strtolower(trim($this->correct_answer));
            
            default:
                return false;
        }
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }
}