<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function getWeeklyQuiz(Request $request, $week)
    {
        $user = $request->user();
        $enrolledCourseIds = $user->enrollments()->pluck('cours_id');
        
        $quiz = Quiz::where('type', 'semaine')
            ->where('semaine', $week)
            ->whereIn('cours_id', $enrolledCourseIds)
            ->with(['questions' => function($query) {
                $query->orderBy('ordre')
                    ->select('id', 'quiz_id', 'question', 'type', 'options', 'ordre')
                    ->with(['answers' => function($q) {
                        $q->select('id', 'question_id', 'reponse');
                    }]);
            }])
            ->first();
            
        if (!$quiz) {
            return response()->json(['message' => 'Quiz non trouvé'], 404);
        }
        
        // Vérifier si l'utilisateur peut tenter le quiz
        if (!$quiz->canAttempt($user)) {
            $attempts = $quiz->userAttempts($user)->get();
            return response()->json([
                'message' => 'Vous ne pouvez plus tenter ce quiz',
                'attempts' => $attempts
            ], 403);
        }
        
        return response()->json($quiz);
    }
    
    public function submitQuiz(Request $request, $id)
    {
        $request->validate([
            'answers' => 'required|array',
            'time_spent' => 'nullable|integer'
        ]);
        
        $quiz = Quiz::findOrFail($id);
        $user = $request->user();
        
        // Vérifier si l'utilisateur peut tenter le quiz
        if (!$quiz->canAttempt($user)) {
            return response()->json([
                'message' => 'Vous ne pouvez plus tenter ce quiz'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            // Calculer le score
            $score = $quiz->calculateScore($request->answers);
            $passed = $score >= $quiz->note_minimale;
            
            // Enregistrer la tentative
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'temps_passe' => $request->time_spent,
                'reponses' => $request->answers,
                'statut' => $passed ? 'reussi' : 'echoue',
                'date_soumission' => now()
            ]);
            
            // Mettre à jour la progression si c'est un quiz de semaine
            if ($quiz->type === 'semaine' && $passed) {
                $enrollment = $user->enrollments()
                    ->where('cours_id', $quiz->cours_id)
                    ->first();
                    
                if ($enrollment) {
                    $progress = $enrollment->progression;
                    $weeklyProgress = 100 / 12; // 12 semaines par cours
                    $newProgress = min(100, $progress + $weeklyProgress);
                    
                    $enrollment->update([
                        'progression' => $newProgress,
                        'semaine_actuelle' => $quiz->semaine
                    ]);
                    
                    // Si le cours est terminé (100%), générer un certificat
                    if ($newProgress >= 100) {
                        $this->generateCertificate($user, $quiz->course);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Quiz soumis avec succès',
                'score' => $score,
                'passed' => $passed,
                'attempt' => $attempt
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la soumission'], 500);
        }
    }
    
    public function getResults(Request $request, $id)
    {
        $user = $request->user();
        $quiz = Quiz::with(['questions.answers'])->findOrFail($id);
        
        $attempts = $quiz->userAttempts($user)
            ->orderByDesc('created_at')
            ->get();
            
        $bestScore = $attempts->max('score');
        $canRetake = $quiz->max_tentatives === null || 
                     $attempts->count() < $quiz->max_tentatives;
        
        return response()->json([
            'quiz' => $quiz,
            'attempts' => $attempts,
            'best_score' => $bestScore,
            'can_retake' => $canRetake,
            'min_score' => $quiz->note_minimale
        ]);
    }
    
    private function generateCertificate($user, $course)
    {
        // Logique de génération de certificat
        // Cette méthode serait implémentée dans le CertificateController
    }
}
