<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;

    protected $table = 'course_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'ordre',
        'actif',
        'meta_titre',
        'meta_description',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Relations
    public function courses()
    {
        return $this->hasMany(Course::class, 'categorie_id');
    }

    public function activeCourses()
    {
        return $this->hasMany(Course::class, 'categorie_id')
            ->where('statut', 'actif');
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
}