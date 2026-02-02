<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'user_id',
        'cours_id',
        'status',
        'progress',
        'enrolled_at',
        'completed_at',
        'certificate_issued',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_issued' => 'boolean',
        'progress' => 'float',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'actif');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'termine');
    }

    // Helper methods
    public function updateProgress()
    {
        $course = $this->course;
        $totalLessons = $course->lessons()->count();
        
        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = LessonProgress::where('user_id', $this->user_id)
            ->whereHas('lesson', function ($query) use ($course) {
                $query->whereHas('module', function ($q) use ($course) {
                    $q->where('cours_id', $course->id);
                });
            })
            ->where('completed', true)
            ->count();

        $progress = ($completedLessons / $totalLessons) * 100;
        $this->update(['progress' => $progress]);

        if ($progress >= 100) {
            $this->update([
                'status' => 'termine',
                'completed_at' => now(),
            ]);
        }

        return $progress;
    }
}