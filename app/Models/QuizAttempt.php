<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $table = 'quiz_attempts';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'temps_passe',
        'reponses',
        'statut',
        'date_soumission',
    ];

    protected $casts = [
        'reponses' => 'array',
        'score' => 'float',
        'date_soumission' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('statut', 'reussi');
    }

    public function scopeFailed($query)
    {
        return $query->where('statut', 'echoue');
    }
}