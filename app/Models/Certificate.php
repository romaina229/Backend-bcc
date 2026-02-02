<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_certificat',
        'user_id',
        'cours_id',
        'nom_complet',
        'titre_cours',
        'date_obtention',
        'note_finale',
        'duree_cours',
        'date_emission',
        'date_expiration',
        'statut',
        'qr_code',
        'url_verification',
        'signataire',
        'fonction_signataire',
        'mention'
    ];

    protected $casts = [
        'date_obtention' => 'datetime',
        'date_emission' => 'datetime',
        'date_expiration' => 'datetime',
        'note_finale' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($certificate) {
            $certificate->numero_certificat = 'CERT-' . Str::upper(Str::random(3)) . '-' . now()->format('Ymd') . '-' . Str::random(6);
            $certificate->url_verification = Str::uuid();
            $certificate->date_emission = now();
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    // Helper methods
    public function isExpired()
    {
        return $this->date_expiration && now()->gt($this->date_expiration);
    }

    public function getVerificationUrl()
    {
        return url('/verifier-certificat/' . $this->url_verification);
    }

    public function generateQRCode()
    {
        $url = $this->getVerificationUrl();
        // Utiliser une bibliothèque QR code ici
        return $url;
    }
}