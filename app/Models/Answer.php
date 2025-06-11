<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'sex',
        'age',
        'height',
        'weight',
        'hypertension',
        'diabetes',
        'level',
        'fitness_goal',
        'fitness_type',
        'walk'
    ];
}
