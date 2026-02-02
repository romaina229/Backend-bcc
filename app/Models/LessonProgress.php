<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    use HasFactory;

    protected $table = 'lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed',
        'progress_percentage',
        'time_spent',
        'completed_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'progress_percentage' => 'float',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // Helper methods
    public function markAsCompleted()
    {
        $this->update([
            'completed' => true,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        // Update enrollment progress
        $enrollment = Enrollment::where('user_id', $this->user_id)
            ->whereHas('course.modules.lessons', function ($query) {
                $query->where('id', $this->lesson_id);
            })
            ->first();

        if ($enrollment) {
            $enrollment->updateProgress();
        }
    }
}