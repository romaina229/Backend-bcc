<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        
        $questions = Question::where('quiz_id', $quizId)
            ->orderBy('ordre')
            ->get()
            ->map(function($question) {
                // Ne pas retourner la réponse correcte
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'options' => $question->options,
                    'points' => $question->points,
                    'ordre' => $question->ordre,
                ];
            });

        return response()->json($questions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizz,id',
            'question' => 'required|string',
            'type' => 'required|in:multiple,unique,texte',
            'options' => 'required_if:type,multiple,unique|array',
            'correct_answer' => 'required|string',
            'points' => 'nullable|numeric|min:0',
            'ordre' => 'nullable|integer',
        ]);

        $question = Question::create([
            'quiz_id' => $request->quiz_id,
            'question' => $request->question,
            'type' => $request->type,
            'options' => $request->options,
            'correct_answer' => $request->correct_answer,
            'points' => $request->points ?? 1,
            'ordre' => $request->ordre ?? 0,
            'explication' => $request->explication,
        ]);

        return response()->json([
            'message' => 'Question créée avec succès',
            'question' => $question
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'sometimes|string',
            'type' => 'sometimes|in:multiple,unique,texte',
            'options' => 'sometimes|array',
            'correct_answer' => 'sometimes|string',
            'points' => 'nullable|numeric|min:0',
        ]);

        $question = Question::findOrFail($id);

        $question->update($request->only([
            'question',
            'type',
            'options',
            'correct_answer',
            'points',
            'ordre',
            'explication',
        ]));

        return response()->json([
            'message' => 'Question mise à jour avec succès',
            'question' => $question
        ]);
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json([
            'message' => 'Question supprimée avec succès'
        ]);
    }

    public function reorder(Request $request, $quizId)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.ordre' => 'required|integer',
        ]);

        foreach ($request->questions as $questionData) {
            Question::where('id', $questionData['id'])
                ->where('quiz_id', $quizId)
                ->update(['ordre' => $questionData['ordre']]);
        }

        return response()->json([
            'message' => 'Ordre des questions mis à jour avec succès'
        ]);
    }
}