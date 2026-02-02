<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()->isAdmin()) {
                return response()->json(['message' => 'Accès refusé'], 403);
            }
            return $next($request);
        });
    }

    // Gestion des utilisateurs
    public function indexUsers(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $role = $request->get('role');
        $status = $request->get('status');
        $search = $request->get('search');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->withCount(['enrollments', 'createdCourses'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($users);
    }

    public function updateUserStatus(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,formateur,participant',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Statut utilisateur mis à jour',
            'user' => $user
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Ne peut pas supprimer un admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'message' => 'Impossible de supprimer le dernier administrateur'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    // Gestion des cours
    public function indexCourses(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');

        $query = Course::with(['category', 'instructor'])
            ->withCount('enrollments');

        if ($status) {
            $query->where('statut', $status);
        }

        $courses = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($courses);
    }

    public function updateCourseStatus(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:brouillon,actif,archive',
        ]);

        $course = Course::findOrFail($id);

        $course->update([
            'statut' => $request->statut,
        ]);

        return response()->json([
            'message' => 'Statut du cours mis à jour',
            'course' => $course
        ]);
    }

    public function deleteCourse($id)
    {
        $course = Course::findOrFail($id);

        // Vérifier s'il y a des inscriptions actives
        $activeEnrollments = Enrollment::where('cours_id', $id)
            ->where('status', 'actif')
            ->count();

        if ($activeEnrollments > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un cours avec des inscriptions actives'
            ], 400);
        }

        $course->delete();

        return response()->json([
            'message' => 'Cours supprimé avec succès'
        ]);
    }

    // Gestion des paiements
    public function indexPayments(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');

        $query = Payment::with(['user', 'course']);

        if ($status) {
            $query->where('statut', $status);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($payments);
    }

    // Statistiques avancées
    public function advancedStats(Request $request)
    {
        $period = $request->get('period', '30days');

        $startDate = match($period) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            '1year' => now()->subYear(),
            default => now()->subDays(30),
        };

        return response()->json([
            'users' => [
                'total' => User::count(),
                'new' => User::where('created_at', '>=', $startDate)->count(),
                'active' => User::whereHas('enrollments', function($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                })->count(),
            ],
            'courses' => [
                'total' => Course::count(),
                'active' => Course::where('statut', 'actif')->count(),
                'new' => Course::where('created_at', '>=', $startDate)->count(),
            ],
            'enrollments' => [
                'total' => Enrollment::count(),
                'new' => Enrollment::where('created_at', '>=', $startDate)->count(),
                'completed' => Enrollment::where('status', 'termine')
                    ->where('completed_at', '>=', $startDate)
                    ->count(),
            ],
            'revenue' => [
                'total' => Payment::where('statut', 'completed')->sum('montant'),
                'period' => Payment::where('statut', 'completed')
                    ->where('created_at', '>=', $startDate)
                    ->sum('montant'),
            ],
        ]);
    }

    // Export des données
    public function exportUsers(Request $request)
    {
        $users = User::all();

        // Ici, implémenter l'export CSV/Excel
        // Pour l'exemple, on retourne les données JSON
        return response()->json([
            'message' => 'Export prêt',
            'count' => $users->count(),
            'data' => $users
        ]);
    }

    public function exportCourses(Request $request)
    {
        $courses = Course::with(['category', 'instructor'])->get();

        return response()->json([
            'message' => 'Export prêt',
            'count' => $courses->count(),
            'data' => $courses
        ]);
    }

    public function exportPayments(Request $request)
    {
        $payments = Payment::with(['user', 'course'])
            ->where('statut', 'completed')
            ->get();

        return response()->json([
            'message' => 'Export prêt',
            'count' => $payments->count(),
            'data' => $payments
        ]);
    }
}