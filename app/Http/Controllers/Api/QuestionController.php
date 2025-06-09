<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    public function index()
    {
        $questions = Question::all()->map(function ($question) {
            return [
                'id' => $question->id,
                'question' => $question->question,
                'choices' => $question->choices,
            ];
        });

        return response()->json($questions);
    }


}
