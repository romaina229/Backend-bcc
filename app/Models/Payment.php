<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cours_id',
        'montant',
        'devise',
        'methode_paiement',
        'statut',
        'transaction_id',
        'reference',
        'date_paiement',
        'metadata',
    ];

    protected $casts = [
        'date_paiement' => 'datetime',
        'montant' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('statut', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('statut', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('statut', 'failed');
    }
}