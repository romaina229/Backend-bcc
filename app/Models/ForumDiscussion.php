<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumDiscussion extends Model
{
    use HasFactory;

    protected $table = 'forum_discussions';

    protected $fillable = [
        'titre',
        'slug',
        'contenu',
        'user_id',
        'categorie_id',
        'cours_id',
        'statut',
        'est_epingle',
        'est_verrouille',
        'nombre_vues',
        'dernier_post_id',
        'tags'
    ];

    protected $casts = [
        'est_epingle' => 'boolean',
        'est_verrouille' => 'boolean',
        'tags' => 'array'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ForumCategory::class, 'categorie_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class, 'discussion_id')->orderBy('created_at');
    }

    public function firstPost()
    {
        return $this->hasOne(ForumPost::class, 'discussion_id')->oldest();
    }

    public function lastPost()
    {
        return $this->belongsTo(ForumPost::class, 'dernier_post_id');
    }

    // Helper methods
    public function incrementViews()
    {
        $this->increment('nombre_vues');
    }

    public function getExcerptAttribute($length = 200)
    {
        $content = strip_tags($this->contenu);
        if (strlen($content) > $length) {
            $content = substr($content, 0, $length) . '...';
        }
        return $content;
    }

    public function getPostCountAttribute()
    {
        return $this->posts()->count();
    }

    // Scopes
    public function scopePinned($query)
    {
        return $query->where('est_epingle', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('est_verrouille', false);
    }

    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }
}