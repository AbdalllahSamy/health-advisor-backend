<?php

namespace App\Http\Controllers\Api\Answer;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\WeeklyPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
                "Level" =>  ucfirst($request->level),
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
                // "home_equipment" => [""],
                // "preferred_language" => "english"
            ]);
        }

        if ($response->failed()) {
            return response()->json(['error' => 'Prediction API call failed.'], 500);
        }

        // Optionally, you can capture the prediction
        $rawPrediction = $response->json();

        if (isset($rawPrediction['weekly_workout_plan'])) {
            foreach ($rawPrediction['weekly_workout_plan'] as $dayIndex => &$dayPlan) {
                // Assign a unique ID for the day
                $dayPlan['id'] = (string) Str::uuid();

                foreach ($dayPlan['exercises'] as $exerciseIndex => &$exercise) {
                    $exercise['id'] = (string) Str::uuid();
                    $exercise['is_done'] = 0; // Set default to not done
                }
            }
        }
        $prediction = $rawPrediction;



        $answer = Answer::create([
            'user_id' => $request->user()->id, // Or use $request->user()->id if needed
            ...$validated,
        ]);
        $type = $request->selected_model === "gym_only" ? "gym_only" : "not_gym_only";

        $weeklyPlan = WeeklyPlan::create([
            'user_id' => $request->user()->id,
            'answer_id' => $answer->id,
            'plan' => $prediction,
            'type' => $type
        ]);

        return response()->json([
            'message' => 'Answer stored successfully.',
            'data' => $answer,
        ]);
    }

    public function markExerciseAsDone(Request $request, string $planId, string $dayId, string $exerciseId)
    {
        $user = Auth::user();

        $plan = WeeklyPlan::where('user_id', $user->id)
            ->where('id', $planId)
            ->firstOrFail();

        $decodedPlan = $plan->plan;

        if (!isset($decodedPlan['weekly_workout_plan'])) {
            return response()->json(['error' => 'Invalid workout plan format.'], 422);
        }

        $found = false;

        foreach ($decodedPlan['weekly_workout_plan'] as &$day) {
            if ($day['id'] === $dayId) {
                foreach ($day['exercises'] as &$exercise) {
                    if ($exercise['id'] === $exerciseId) {
                        $exercise['is_done'] = 1;
                        $found = true;
                        break;
                    }
                }
                break;
            }
        }

        if (!$found) {
            return response()->json(['error' => 'Exercise or Day not found.'], 404);
        }

        $plan->plan = $decodedPlan;
        $plan->save();

        return response()->json([
            'message' => 'Exercise marked as done successfully.',
            'plan' => $plan
        ]);
    }

    public function setDayFeedback(Request $request, string $planId, string $dayId)
    {
        $request->validate([
            'rate' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();

        $plan = WeeklyPlan::where('user_id', $user->id)
            ->where('id', $planId)
            ->firstOrFail();

        $decodedPlan = $plan->plan;

        if (!isset($decodedPlan['weekly_workout_plan'])) {
            return response()->json(['error' => 'Invalid workout plan format.'], 422);
        }

        $feedbackLabel = match (true) {
            $request->rate <= 2 => 'easy',
            $request->rate == 3 => 'medium',
            $request->rate >= 4 => 'hard',
        };

        $found = false;

        foreach ($decodedPlan['weekly_workout_plan'] as &$day) {
            if ($day['id'] === $dayId) {
                $day['feedback'] = $feedbackLabel;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json(['error' => 'Workout day not found.'], 404);
        }

        $plan->plan = $decodedPlan;
        $plan->save();

        $allDaysRated = collect($decodedPlan['weekly_workout_plan'])->every(function ($day) {
            return isset($day['feedback']) && !empty($day['feedback']);
        });

        $externalResponse = null;

        if ($allDaysRated) {
            $decodedPlan['comment'] = 'Great job finishing the week!';

            // Save the comment back to the plan
            $plan->plan = $decodedPlan;
            $plan->save();

            // 🔁 Send the full plan to the feedback API
            $externalResponse = Http::post('http://localhost:8001/api/feedback', $decodedPlan);
            $rawPrediction = $externalResponse->json();

            if (isset($rawPrediction['weekly_workout_plan'])) {
                foreach ($rawPrediction['weekly_workout_plan'] as $dayIndex => &$dayPlan) {
                    // Assign a unique ID for the day
                    $dayPlan['id'] = (string) Str::uuid();

                    foreach ($dayPlan['exercises'] as $exerciseIndex => &$exercise) {
                        $exercise['id'] = (string) Str::uuid();
                        $exercise['is_done'] = 0; // Set default to not done
                    }
                }
            }
            $prediction = $rawPrediction;
            $answer = Answer::where('user_id', $request->user()->id)->first();
            $weeklyPlan = WeeklyPlan::create([
                'user_id' => $request->user()->id,
                'answer_id' => $answer->id,
                'plan' => $prediction,
                'type' => 'not_gym_only'
            ]);
        }



        return response()->json([
            'message' => 'Feedback saved successfully.',
            'feedback' => $feedbackLabel,
            'plan' => $plan
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
