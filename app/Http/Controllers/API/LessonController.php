<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Request $request, $id)
    {
        $lesson = Lesson::with(['module.course'])->findOrFail($id);
        $user = $request->user();

        // Vérifier l'accès
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $lesson->module->cours_id)
            ->first();

        if (!$enrollment && !$lesson->gratuit) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        // Récupérer la progression
        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $id)
            ->first();

        return response()->json([
            'lesson' => $lesson,
            'progress' => $progress
        ]);
    }

    public function complete(Request $request, $id)
    {
        $user = $request->user();
        $lesson = Lesson::findOrFail($id);

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $id,
            ],
            [
                'completed' => true,
                'progress_percentage' => 100,
                'completed_at' => now(),
            ]
        );

        // Mettre à jour la progression du cours
        $progress->markAsCompleted();

        return response()->json([
            'message' => 'Leçon marquée comme terminée',
            'progress' => $progress
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:200',
            'module_id' => 'required|exists:modules,id',
            'type' => 'required|in:video,texte,quiz,ressource',
            'duree' => 'nullable|integer',
        ]);

        $lesson = Lesson::create([
            'titre' => $request->titre,
            'slug' => \Str::slug($request->titre) . '-' . \Str::random(6),
            'description' => $request->description,
            'module_id' => $request->module_id,
            'type' => $request->type,
            'contenu' => $request->contenu,
            'video_url' => $request->video_url,
            'duree' => $request->duree,
            'ordre' => $request->ordre ?? 0,
            'gratuit' => $request->gratuit ?? false,
        ]);

        return response()->json([
            'message' => 'Leçon créée avec succès',
            'lesson' => $lesson
        ], 201);
    }
}