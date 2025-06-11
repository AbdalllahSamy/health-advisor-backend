<?php

namespace App\Http\Controllers\Api\Answer;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sex' => 'nullable|string',
            'age' => 'nullable|integer',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'hypertension' => 'nullable|string',
            'diabetes' => 'nullable|string',
            'level' => 'nullable|string',
            'fitness_goal' => 'nullable|string',
            'fitness_type' => 'nullable|string',
            'walk' => 'nullable'
        ]);

        $answer = Answer::create([
            'user_id' => $request->user()->id, // Or use $request->user()->id if needed
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Answer stored successfully.',
            'data' => $answer,
        ]);
    }
}
