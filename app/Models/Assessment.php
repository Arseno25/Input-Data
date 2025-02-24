<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $fillable = [
        'student_id',
//        'room_id',
        'lecturer_id',
        'assessment_stage',
        'assessment',
    ];

    protected $casts = [
        'assessment' => 'array',
    ];

//    public function room(): BelongsTo
//    {
//        return $this->belongsTo(Room::class);
//    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

}
