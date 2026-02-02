<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Certificate;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function studentDashboard(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $totalCourses = Enrollment::where('user_id', $user->id)->count();
        $completedCourses = Enrollment::where('user_id', $user->id)
            ->where('status', 'termine')
            ->count();
        
        $certificates = Certificate::where('user_id', $user->id)->count();
        
        $recentQuizzes = QuizAttempt::where('user_id', $user->id)
            ->with('quiz.course')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalLearningTime = Enrollment::where('user_id', $user->id)
            ->join('courses', 'enrollments.cours_id', '=', 'courses.id')
            ->sum('courses.duree');

        return response()->json([
            'enrollments' => $enrollments,
            'stats' => [
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'certificates' => $certificates,
                'learning_time' => $totalLearningTime,
                'completion_rate' => $totalCourses > 0 ? round(($completedCourses / $totalCourses) * 100, 2) : 0,
            ],
            'recent_quizzes' => $recentQuizzes,
        ]);
    }

    public function instructorDashboard(Request $request)
    {
        $user = $request->user();

        $courses = Course::where('instructor_id', $user->id)
            ->withCount('enrollments')
            ->get();

        $totalStudents = Enrollment::whereIn('cours_id', $courses->pluck('id'))->count();
        
        $totalRevenue = Payment::whereIn('cours_id', $courses->pluck('id'))
            ->where('statut', 'completed')
            ->sum('montant');

        $courseStats = Course::where('instructor_id', $user->id)
            ->withCount('enrollments')
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'titre' => $course->titre,
                    'enrollments_count' => $course->enrollments_count,
                    'revenue' => Payment::where('cours_id', $course->id)
                        ->where('statut', 'completed')
                        ->sum('montant'),
                ];
            });

        return response()->json([
            'courses' => $courses,
            'stats' => [
                'total_courses' => $courses->count(),
                'total_students' => $totalStudents,
                'total_revenue' => $totalRevenue,
                'active_courses' => $courses->where('statut', 'actif')->count(),
            ],
            'course_stats' => $courseStats,
        ]);
    }

    public function adminDashboard(Request $request)
    {
        $totalUsers = User::count();
        $totalCourses = Course::count();
        $totalEnrollments = Enrollment::count();
        $totalRevenue = Payment::where('statut', 'completed')->sum('montant');
        $totalCertificates = Certificate::count();

        // Statistiques par mois (6 derniers mois)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'enrollments' => Enrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'revenue' => Payment::where('statut', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('montant'),
            ];
        }

        // Cours les plus populaires
        $popularCourses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // Utilisateurs récents
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_users' => $totalUsers,
                'total_courses' => $totalCourses,
                'total_enrollments' => $totalEnrollments,
                'total_revenue' => $totalRevenue,
                'total_certificates' => $totalCertificates,
            ],
            'monthly_stats' => $monthlyStats,
            'popular_courses' => $popularCourses,
            'recent_users' => $recentUsers,
        ]);
    }

    public function statistics(Request $request)
    {
        return response()->json([
            'users' => [
                'total' => User::count(),
                'students' => User::where('role', 'participant')->count(),
                'instructors' => User::where('role', 'formateur')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'courses' => [
                'total' => Course::count(),
                'active' => Course::where('statut', 'actif')->count(),
                'draft' => Course::where('statut', 'brouillon')->count(),
            ],
            'enrollments' => [
                'total' => Enrollment::count(),
                'active' => Enrollment::where('status', 'actif')->count(),
                'completed' => Enrollment::where('status', 'termine')->count(),
            ],
            'revenue' => [
                'total' => Payment::where('statut', 'completed')->sum('montant'),
                'this_month' => Payment::where('statut', 'completed')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->sum('montant'),
            ],
        ]);
    }

    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(6));
        $endDate = $request->get('end_date', now());

        $enrollmentReport = Enrollment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueReport = Payment::where('statut', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(montant) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'enrollment_report' => $enrollmentReport,
            'revenue_report' => $revenueReport,
        ]);
    }
}