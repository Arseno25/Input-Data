<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $fillable = [
        'student_id',
        'room_id',
        'lecturer_id',
        'assessment_name',
        'score_1',
        'score_2',
        'score_3',
        'score_4',
        'score_5',
        'score_6',
        'score_7',
        'score_8',
    ];

    protected $casts = [
        'score' => 'json',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function getScoreAsStringAttribute(): string
    {
        return json_encode($this->score);
    }

}
