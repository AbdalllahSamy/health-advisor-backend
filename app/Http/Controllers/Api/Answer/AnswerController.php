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
        $user = Auth::user();
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
        if ($request->selected_model === "gym_only") {
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
        } else {
            $response = Http::post('http://127.0.0.1:8000/generate_plan', [
                "name" => $user->name,
                "age" => $request->age,
                "gender" => $request->sex,
                "height_cm" => $request->height,
                "weight_kg" => $request->weight,
                "fitness_level" => $request->fitness_level,
                "goal" => $request->fitness_goal,
                "health_conditions" => [],
                "food_allergies" => $request->allergies,
                "diet_preference" => $request->diet_preference,
                "days_per_week" => $request->days_per_week,
                "workout_duration_minutes" => $request->workout_duration_minutes,
                "has_gym_access" => $request->has_gym_access,
                "home_equipment" => [""],
                "preferred_language" => "english"
            ]);
        }

        if ($response->failed()) {
            return response()->json(['error' => 'Prediction API call failed.'], 500);
        }

        // Optionally, you can capture the prediction
        $prediction = $response->json();


        $answer = Answer::create([
            'user_id' => $request->user()->id, // Or use $request->user()->id if needed
            ...$validated,
        ]);

        $weeklyPlan = WeeklyPlan::create([
            'user_id' => $request->user()->id,
            'answer_id' => $answer->id,
            'plan' => $prediction,
            'type' => $request->selected_model === "gym_only" ? "gym_only" : "not_gym_only"
        ]);

        return response()->json([
            'message' => 'Answer stored successfully.',
            'data' => $answer,
        ]);
    }

    public function plans()
    {
        $user = Auth::user();

        // Get plans ordered by id ASC
        $plans = WeeklyPlan::where('user_id', $user->id)
            ->orderBy('id', 'asc')
            ->get();

        // Add week index to each plan
        $plansWithWeek = $plans->values()->map(function ($plan, $index) {
            $plan->week = 'Week ' . ($index + 1);
            return $plan;
        });

        return response()->json([
            'message' => 'Plans successfully.',
            'data' => $plansWithWeek,
        ]);
    }

    public function plansById(string $id)
    {
        $user = Auth::user();
        $plans = WeeklyPlan::where('user_id', $user->id)->where('id', $id)->first();
        return response()->json([
            'message' => 'Plan successfully.',
            'data' => $plans,
        ]);
    }
}
