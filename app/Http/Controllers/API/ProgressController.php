<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function courseProgress(Request $request, $id)
    {
        $user = $request->user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $id)
            ->with(['course.modules.lessons'])
            ->firstOrFail();

        $course = $enrollment->course;
        $totalLessons = $course->lessons()->count();
        
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->whereHas('lesson.module', function($query) use ($id) {
                $query->where('cours_id', $id);
            })
            ->where('completed', true)
            ->count();

        $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

        // Détails par module
        $moduleProgress = [];
        foreach ($course->modules as $module) {
            $moduleLessons = $module->lessons->count();
            $moduleCompleted = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $module->lessons->pluck('id'))
                ->where('completed', true)
                ->count();

            $moduleProgress[] = [
                'module' => $module->titre,
                'total_lessons' => $moduleLessons,
                'completed_lessons' => $moduleCompleted,
                'progress' => $moduleLessons > 0 ? ($moduleCompleted / $moduleLessons) * 100 : 0,
            ];
        }

        return response()->json([
            'enrollment' => $enrollment,
            'overall_progress' => round($progress, 2),
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'module_progress' => $moduleProgress,
        ]);
    }
}