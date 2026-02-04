<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show($id)
    {
        $user = auth()->user();
        
        $quiz = Quiz::with(['questions' => function($query) {
            $query->orderBy('ordre')
                ->select('id', 'quiz_id', 'question', 'type', 'options', 'ordre', 'points');
        }])->findOrFail($id);
        
        // Vérifier si l'utilisateur peut tenter le quiz
        if (!$this->canAttemptQuiz($user, $quiz)) {
            $attempts = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $id)
                ->get();
            
            return response()->json([
                'message' => 'Vous ne pouvez plus tenter ce quiz',
                'attempts' => $attempts
            ], 403);
        }
        
        return response()->json($quiz);
    }
    
    public function getWeeklyQuiz(Request $request, $week)
    {
        $user = $request->user();
        
        // Récupérer les cours auxquels l'utilisateur est inscrit
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->where('status', 'actif')
            ->pluck('cours_id');
        
        $quiz = Quiz::where('type', 'semaine')
            ->where('semaine', $week)
            ->whereIn('cours_id', $enrolledCourseIds)
            ->with(['questions' => function($query) {
                $query->orderBy('ordre')
                    ->select('id', 'quiz_id', 'question', 'type', 'options', 'ordre', 'points');
            }])
            ->first();
            
        if (!$quiz) {
            return response()->json(['message' => 'Quiz non trouvé'], 404);
        }
        
        // Vérifier si l'utilisateur peut tenter le quiz
        if (!$this->canAttemptQuiz($user, $quiz)) {
            $attempts = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->get();
            
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
        
        $quiz = Quiz::with('questions')->findOrFail($id);
        $user = $request->user();
        
        // Vérifier si l'utilisateur peut tenter le quiz
        if (!$this->canAttemptQuiz($user, $quiz)) {
            return response()->json([
                'message' => 'Vous ne pouvez plus tenter ce quiz'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            // Calculer le score
            $score = $this->calculateScore($quiz, $request->answers);
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
                $this->updateCourseProgress($user, $quiz);
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
            return response()->json([
                'message' => 'Erreur lors de la soumission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getResults(Request $request, $id)
    {
        $user = $request->user();
        $quiz = Quiz::with(['questions'])->findOrFail($id);
        
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $id)
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
    
    public function quizHistory(Request $request)
    {
        $user = $request->user();
        
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->with('quiz.course')
            ->orderByDesc('created_at')
            ->paginate(15);
        
        return response()->json($attempts);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string',
            'cours_id' => 'required|exists:courses,id',
            'module_id' => 'nullable|exists:modules,id',
            'type' => 'required|in:semaine,module,final',
            'semaine' => 'required_if:type,semaine|nullable|integer',
            'duree' => 'nullable|integer',
            'note_minimale' => 'nullable|integer|min:0|max:100',
            'max_tentatives' => 'nullable|integer|min:1',
        ]);
        
        $quiz = Quiz::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'cours_id' => $request->cours_id,
            'module_id' => $request->module_id,
            'type' => $request->type,
            'semaine' => $request->semaine,
            'duree' => $request->duree,
            'note_minimale' => $request->note_minimale ?? 70,
            'max_tentatives' => $request->max_tentatives,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => $request->statut ?? 'brouillon',
            'instructions' => $request->instructions,
            'ordre' => $request->ordre ?? 0,
        ]);
        
        return response()->json([
            'message' => 'Quiz créé avec succès',
            'quiz' => $quiz
        ], 201);
    }
    
    public function getQuizResponses(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        
        // Vérifier que l'utilisateur est formateur du cours
        if ($quiz->course->instructor_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);
        
        return response()->json($attempts);
    }
    
    // Méthodes privées
    private function canAttemptQuiz($user, $quiz)
    {
        if ($quiz->max_tentatives === null) {
            return true;
        }
        
        $attemptCount = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();
        
        return $attemptCount < $quiz->max_tentatives;
    }
    
    private function calculateScore($quiz, $answers)
    {
        $totalPoints = 0;
        $earnedPoints = 0;
        
        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points ?? 1;
            
            $userAnswer = $answers[$question->id] ?? null;
            
            if ($this->isAnswerCorrect($question, $userAnswer)) {
                $earnedPoints += $question->points ?? 1;
            }
        }
        
        return $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    }
    
    private function isAnswerCorrect($question, $userAnswer)
    {
        if ($userAnswer === null) {
            return false;
        }
        
        switch ($question->type) {
            case 'multiple':
            case 'unique':
                return $userAnswer === $question->correct_answer;
            
            case 'texte':
                return strtolower(trim($userAnswer)) === strtolower(trim($question->correct_answer));
            
            default:
                return false;
        }
    }
    
    private function updateCourseProgress($user, $quiz)
    {
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $quiz->cours_id)
            ->first();
        
        if (!$enrollment) {
            return;
        }
        
        // Calculer la progression basée sur les semaines
        $totalWeeks = Quiz::where('cours_id', $quiz->cours_id)
            ->where('type', 'semaine')
            ->distinct('semaine')
            ->count();
        
        if ($totalWeeks === 0) {
            return;
        }
        
        $completedWeeks = QuizAttempt::where('user_id', $user->id)
            ->where('statut', 'reussi')
            ->whereHas('quiz', function($q) use ($quiz) {
                $q->where('cours_id', $quiz->cours_id)
                  ->where('type', 'semaine');
            })
            ->distinct('quiz_id')
            ->count();
        
        $progress = ($completedWeeks / $totalWeeks) * 100;
        
        $enrollment->update([
            'progress' => min(100, $progress)
        ]);
        
        // Si 100%, marquer comme terminé
        if ($progress >= 100) {
            $enrollment->update([
                'status' => 'termine',
                'completed_at' => now()
            ]);
        }
    }
}