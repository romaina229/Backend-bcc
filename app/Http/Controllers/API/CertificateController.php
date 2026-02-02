<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $certificates = Certificate::where('user_id', $user->id)
            ->with('course')
            ->orderBy('date_obtention', 'desc')
            ->paginate(15);

        return response()->json($certificates);
    }

    public function show($id)
    {
        $certificate = Certificate::with(['user', 'course'])->findOrFail($id);

        // Vérifier l'autorisation
        if ($certificate->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($certificate);
    }

    public function verify($uuid)
    {
        $certificate = Certificate::where('url_verification', $uuid)->firstOrFail();

        return response()->json([
            'valid' => true,
            'certificate' => [
                'numero' => $certificate->numero_certificat,
                'nom_complet' => $certificate->nom_complet,
                'titre_cours' => $certificate->titre_cours,
                'date_obtention' => $certificate->date_obtention,
                'statut' => $certificate->statut,
                'expired' => $certificate->isExpired(),
            ]
        ]);
    }

    public function download($id)
    {
        $certificate = Certificate::findOrFail($id);

        // Vérifier l'autorisation
        if ($certificate->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Générer le PDF
        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate
        ]);

        return $pdf->download('certificat-' . $certificate->numero_certificat . '.pdf');
    }

    public function generateForCourse(Request $request, $courseId)
    {
        $user = $request->user();
        $course = Course::findOrFail($courseId);

        // Vérifier que l'utilisateur a terminé le cours
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $courseId)
            ->where('status', 'termine')
            ->where('progress', '>=', 100)
            ->firstOrFail();

        // Vérifier si un certificat existe déjà
        $existing = Certificate::where('user_id', $user->id)
            ->where('cours_id', $courseId)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Certificat déjà généré',
                'certificate' => $existing
            ], 400);
        }

        // Calculer la note finale (moyenne des quiz)
        $finalScore = $user->quizAttempts()
            ->whereHas('quiz', function($q) use ($courseId) {
                $q->where('cours_id', $courseId);
            })
            ->where('statut', 'reussi')
            ->avg('score');

        // Déterminer la mention
        $mention = null;
        if ($finalScore >= 90) {
            $mention = 'Très Bien';
        } elseif ($finalScore >= 80) {
            $mention = 'Bien';
        } elseif ($finalScore >= 70) {
            $mention = 'Assez Bien';
        }

        // Créer le certificat
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'cours_id' => $courseId,
            'nom_complet' => $user->name,
            'titre_cours' => $course->titre,
            'date_obtention' => now(),
            'note_finale' => $finalScore,
            'duree_cours' => $course->duree,
            'statut' => 'valide',
            'signataire' => 'Directeur Général',
            'fonction_signataire' => 'BCC Center',
            'mention' => $mention,
        ]);

        // Mettre à jour l'inscription
        $enrollment->update(['certificate_issued' => true]);

        return response()->json([
            'message' => 'Certificat généré avec succès',
            'certificate' => $certificate
        ], 201);
    }

    public function revoke(Request $request, $id)
    {
        // Seul un admin peut révoquer
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $certificate = Certificate::findOrFail($id);

        $certificate->update([
            'statut' => 'revoque',
        ]);

        return response()->json([
            'message' => 'Certificat révoqué avec succès',
            'certificate' => $certificate
        ]);
    }
}