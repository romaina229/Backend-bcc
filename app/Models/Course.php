<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'slug',
        'description',
        'description_longue',
        'categorie_id',
        'instructor_id',
        'niveau',
        'duree',
        'prix',
        'prix_promotion',
        'image',
        'video_presentation',
        'objectifs',
        'prerequis',
        'public_cible',
        'certification',
        'langue',
        'statut',
        'date_debut',
        'date_fin',
        'places_disponibles',
        'places_limite',
        'ordre',
        'meta_titre',
        'meta_description',
        'tags'
    ];

    protected $casts = [
        'objectifs' => 'array',
        'prerequis' => 'array',
        'public_cible' => 'array',
        'tags' => 'array',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'prix' => 'decimal:2',
        'prix_promotion' => 'decimal:2'
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'categorie_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('ordre');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['statut', 'progression', 'date_inscription'])
            ->withTimestamps();
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function weeklyQuizzes()
    {
        return $this->hasMany(Quiz::class)->where('type', 'semaine');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function forumCategory()
    {
        return $this->hasOne(ForumCategory::class);
    }

    public function getCurrentPriceAttribute()
    {
        return $this->prix_promotion ?: $this->prix;
    }

    public function getEnrollmentCountAttribute()
    {
        return $this->enrollments()->where('statut', 'actif')->count();
    }

    public function getCompletionRateAttribute()
    {
        $total = $this->enrollments()->where('statut', 'actif')->count();
        if ($total === 0) return 0;
        
        $completed = $this->enrollments()
            ->where('statut', 'actif')
            ->where('progression', '>=', 100)
            ->count();
            
        return round(($completed / $total) * 100, 2);
    }
}
