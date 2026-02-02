<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

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
        'prix_promotion' => 'decimal:2',
        'certification' => 'boolean',
    ];

    // Relations
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
        return $this->hasMany(Module::class, 'cours_id')->orderBy('ordre');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Module::class, 'cours_id', 'module_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'cours_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'cours_id', 'user_id')
            ->withPivot(['status', 'progress', 'enrolled_at'])
            ->withTimestamps();
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'cours_id');
    }

    public function weeklyQuizzes()
    {
        return $this->hasMany(Quiz::class, 'cours_id')->where('type', 'semaine');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'cours_id');
    }

    public function forumCategory()
    {
        return $this->hasOne(ForumCategory::class, 'cours_id');
    }

    // Accessors
    public function getCurrentPriceAttribute()
    {
        return $this->prix_promotion ?: $this->prix;
    }

    public function getEnrollmentCountAttribute()
    {
        return $this->enrollments()->where('status', 'actif')->count();
    }

    public function getCompletionRateAttribute()
    {
        $total = $this->enrollments()->where('status', 'actif')->count();
        if ($total === 0) return 0;
        
        $completed = $this->enrollments()
            ->where('status', 'actif')
            ->where('progress', '>=', 100)
            ->count();
            
        return round(($completed / $total) * 100, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('categorie_id', $categoryId);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }
}