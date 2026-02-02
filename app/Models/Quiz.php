<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'cours_id',
        'module_id',
        'type', // semaine, module, final
        'semaine',
        'duree',
        'note_minimale',
        'max_tentatives',
        'date_debut',
        'date_fin',
        'statut',
        'instructions',
        'ordre'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'instructions' => 'array'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('ordre');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function userAttempts(User $user)
    {
        return $this->attempts()->where('user_id', $user->id);
    }

    public function canAttempt(User $user)
    {
        if ($this->statut !== 'actif') return false;
        
        $attemptsCount = $this->userAttempts($user)->count();
        if ($this->max_tentatives && $attemptsCount >= $this->max_tentatives) return false;
        
        $now = now();
        if ($this->date_debut && $now->lt($this->date_debut)) return false;
        if ($this->date_fin && $now->gt($this->date_fin)) return false;
        
        return true;
    }

    public function calculateScore(array $answers)
    {
        $totalQuestions = $this->questions()->count();
        if ($totalQuestions === 0) return 0;
        
        $correct = 0;
        foreach ($this->questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            if ($question->isCorrect($userAnswer)) {
                $correct++;
            }
        }
        
        return round(($correct / $totalQuestions) * 100, 2);
    }
}
