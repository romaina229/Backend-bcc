<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        // Vérifier si déjà inscrit
        $existing = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à ce cours'
            ], 400);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'cours_id' => $id,
            'status' => 'actif',
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Inscription réussie',
            'enrollment' => $enrollment
        ], 201);
    }

    public function courseStudents(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        // Vérifier que l'utilisateur est l'instructeur
        if ($course->instructor_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $enrollments = Enrollment::where('cours_id', $id)
            ->with('user')
            ->paginate(20);

        return response()->json($enrollments);
    }
}