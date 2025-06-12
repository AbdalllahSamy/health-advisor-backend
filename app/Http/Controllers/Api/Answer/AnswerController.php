<?php

namespace App\Http\Controllers\Api\Answer;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\WeeklyPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        $response = Http::post('http://127.0.0.1:5000/predict', [
            "Sex" => $request->sex,
            "Age" => $request->age,
            "Height" => $request->weight,
            "Weight" => $request->height,
            "Hypertension" => $request->hypertension,
            "Diabetes" => $request->diabetes,
            "Level" => $request->level,
            "Fitness Goal" => $request->fitness_goal,
            "Fitness Type" => $request->fitness_type
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Prediction API call failed.'], 500);
        }

        // Optionally, you can capture the prediction
        $prediction = $response->json();

        dd($prediction);

        $answer = Answer::create([
            'user_id' => $request->user()->id, // Or use $request->user()->id if needed
            ...$validated,
        ]);

        $weeklyPlan = WeeklyPlan::create([
            'user_id' => $request->user()->id,
            'answer_id' => $answer->id,
            'plan' => $prediction,
        ]);

        return response()->json([
            'message' => 'Answer stored successfully.',
            'data' => $answer,
        ]);
    }

    public function plans(){
        $user = Auth::user();
        $plans = WeeklyPlan::where('user_id', $user->id)->get();
         return response()->json([
            'message' => 'Plans successfully.',
            'data' => $plans,
        ]);
    }

    public function plansById(string $id){
        $user = Auth::user();
        $plans = WeeklyPlan::where('user_id', $user->id)->where('id', $id)->first();
         return response()->json([
            'message' => 'Plan successfully.',
            'data' => $plans,
        ]);
    }
}
