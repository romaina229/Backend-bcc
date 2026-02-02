<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function indexPublic(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $categoryId = $request->get('category_id');
        $niveau = $request->get('niveau');
        $search = $request->get('search');

        $query = Course::with(['category', 'instructor'])
            ->where('statut', 'actif')
            ->withCount('enrollments');

        if ($categoryId) {
            $query->where('categorie_id', $categoryId);
        }

        if ($niveau) {
            $query->where('niveau', $niveau);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('titre', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $courses = $query->paginate($perPage);

        return response()->json($courses);
    }

    public function showPublic($id)
    {
        $course = Course::with([
            'category',
            'instructor',
            'modules.lessons',
        ])->findOrFail($id);

        return response()->json($course);
    }

    public function myCourses(Request $request)
    {
        $user = $request->user();
        
        $enrollments = Enrollment::where('user_id', $user->id)
            ->with(['course.category', 'course.instructor'])
            ->paginate(15);

        return response()->json($enrollments);
    }

    public function content(Request $request, $id)
    {
        $user = $request->user();
        
        // Vérifier que l'utilisateur est inscrit
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $id)
            ->firstOrFail();

        $course = Course::with([
            'modules' => function($query) {
                $query->where('actif', true)->orderBy('ordre');
            },
            'modules.lessons' => function($query) {
                $query->where('actif', true)->orderBy('ordre');
            }
        ])->findOrFail($id);

        return response()->json([
            'course' => $course,
            'enrollment' => $enrollment
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:200',
            'description' => 'required|string',
            'categorie_id' => 'required|exists:course_categories,id',
            'niveau' => 'required|in:debutant,intermediaire,avance,expert',
            'duree' => 'required|integer',
            'prix' => 'required|numeric|min:0',
        ]);

        $course = Course::create([
            'titre' => $request->titre,
            'slug' => Str::slug($request->titre) . '-' . Str::random(6),
            'description' => $request->description,
            'description_longue' => $request->description_longue,
            'categorie_id' => $request->categorie_id,
            'instructor_id' => $request->user()->id,
            'niveau' => $request->niveau,
            'duree' => $request->duree,
            'prix' => $request->prix,
            'prix_promotion' => $request->prix_promotion,
            'image' => $request->image,
            'objectifs' => $request->objectifs,
            'prerequis' => $request->prerequis,
            'statut' => 'brouillon',
        ]);

        return response()->json([
            'message' => 'Cours créé avec succès',
            'course' => $course
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        // Vérifier que l'utilisateur est l'instructeur ou admin
        if ($course->instructor_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $course->update($request->only([
            'titre',
            'description',
            'description_longue',
            'categorie_id',
            'niveau',
            'duree',
            'prix',
            'prix_promotion',
            'image',
            'objectifs',
            'prerequis',
            'statut',
        ]));

        return response()->json([
            'message' => 'Cours mis à jour avec succès',
            'course' => $course
        ]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Cours supprimé avec succès']);
    }

    public function instructorCourses(Request $request)
    {
        $courses = Course::where('instructor_id', $request->user()->id)
            ->with(['category'])
            ->withCount('enrollments')
            ->paginate(15);

        return response()->json($courses);
    }
}