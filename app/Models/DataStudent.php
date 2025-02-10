<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataStudent extends Model
{
    protected $fillable = [
        'name',
        'nim',
        'score',
    ];
}
