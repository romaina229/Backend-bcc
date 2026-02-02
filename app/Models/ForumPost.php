<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory;

    protected $table = 'forum_posts';

    protected $fillable = [
        'contenu',
        'user_id',
        'discussion_id',
        'est_premier_post',
        'est_meilleure_reponse',
    ];

    protected $casts = [
        'est_premier_post' => 'boolean',
        'est_meilleure_reponse' => 'boolean',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discussion()
    {
        return $this->belongsTo(ForumDiscussion::class, 'discussion_id');
    }

    // Scopes
    public function scopeFirstPosts($query)
    {
        return $query->where('est_premier_post', true);
    }

    public function scopeBestAnswers($query)
    {
        return $query->where('est_meilleure_reponse', true);
    }
}