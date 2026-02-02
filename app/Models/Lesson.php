<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lessons';

    protected $fillable = [
        'titre',
        'slug',
        'description',
        'module_id',
        'type',
        'contenu',
        'video_url',
        'duree',
        'ordre',
        'gratuit',
        'actif',
        'objectifs',
        'ressources',
    ];

    protected $casts = [
        'gratuit' => 'boolean',
        'actif' => 'boolean',
        'objectifs' => 'array',
        'ressources' => 'array',
    ];

    // Relations
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class, 'lesson_id');
    }

    // Helper methods
    public function isCompletedBy(User $user)
    {
        return $this->progress()
            ->where('user_id', $user->id)
            ->where('completed', true)
            ->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('actif', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    public function scopeFree($query)
    {
        return $query->where('gratuit', true);
    }
}