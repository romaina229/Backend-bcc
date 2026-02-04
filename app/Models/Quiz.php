<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizz';

    protected $fillable = [
        'titre',
        'description',
        'cours_id',
        'module_id',
        'type',
        'semaine',
        'duree',
        'note_minimale',
        'max_tentatives',
        'date_debut',
        'date_fin',
        'statut',
        'instructions',
        'ordre',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'instructions' => 'array',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('ordre');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function userAttempts($user)
    {
        return $this->attempts()->where('user_id', $user->id);
    }

    // Helper methods
    public function canAttempt($user)
    {
        if ($this->max_tentatives === null) {
            return true;
        }
        
        $attemptCount = $this->userAttempts($user)->count();
        return $attemptCount < $this->max_tentatives;
    }

    public function isActive()
    {
        if ($this->statut !== 'actif') {
            return false;
        }

        $now = now();

        if ($this->date_debut && $now->lt($this->date_debut)) {
            return false;
        }

        if ($this->date_fin && $now->gt($this->date_fin)) {
            return false;
        }

        return true;
    }

    public function calculateScore($answers)
    {
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($this->questions as $question) {
            $totalPoints += $question->points ?? 1;
            
            $userAnswer = $answers[$question->id] ?? null;
            
            if ($question->isCorrect($userAnswer)) {
                $earnedPoints += $question->points ?? 1;
            }
        }

        return $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'semaine');
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('cours_id', $courseId);
    }
}