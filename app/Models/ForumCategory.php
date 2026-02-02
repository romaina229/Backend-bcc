<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    use HasFactory;

    protected $table = 'forum_categories';

    protected $fillable = [
        'nom',
        'description',
        'ordre',
        'couleur',
        'icon',
        'cours_id',
    ];

    // Relations
    public function discussions()
    {
        return $this->hasMany(ForumDiscussion::class, 'categorie_id');
    }

    public function posts()
    {
        return $this->hasManyThrough(ForumPost::class, ForumDiscussion::class, 'categorie_id', 'discussion_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function lastPost()
    {
        return $this->hasOneThrough(
            ForumPost::class,
            ForumDiscussion::class,
            'categorie_id',
            'discussion_id'
        )->latest();
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }
}