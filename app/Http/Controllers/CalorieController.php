<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalorieController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'nullable|numeric',         // in kg
            'height' => 'nullable|numeric',         // in meters (for BMI)
            'age' => 'nullable|numeric',
            'gender' => 'nullable|in:male,female',
            'activity_level' => 'nullable|in:sedentary,moderate,active,very_active',
            'type' => 'required|in:BMI,water,protine,BMR'
        ]);

        $type = $validated['type'];
        $weight = $validated['weight'] ?? 0;
        $height = $validated['height'] ?? 0;
        $age = $validated['age'] ?? 0;
        $gender = $validated['gender'] ?? null;

        switch ($type) {
            case 'BMI':
                if ($height <= 0) {
                    return response()->json(['error' => 'Height must be greater than zero for BMI calculation.'], 422);
                }
                $result = $weight / ($height * $height);
                break;

            case 'water':
                $result = $weight * 0.03;
                break;

            case 'protine':
                $result = $weight * 1.75;
                break;

            case 'BMR':
                if ($gender === 'male') {
                    $result = 10 * $weight + 6.25 * $height - 5 * $age + 5;
                } elseif ($gender === 'female') {
                    $result = 10 * $weight + 6.25 * $height - 5 * $age - 161;
                } else {
                    return response()->json(['error' => 'Gender is required for BMR calculation.'], 422);
                }
                break;

            default:
                return response()->json(['error' => 'Invalid calculation type.'], 422);
        }

        return response()->json([
            'type' => $type,
            'result' => round($result, 2),
        ]);
    }

    public function analysis()
    {
        $user = Auth::user();
        $answers = Answer::where('user_id', $user->id)->first();
        $bmi = null;
        $water = null;
        $protein = null;
        $bmr = null;

        // Prevent division by zero
        if (!empty($answers->height) && $answers->height > 0) {
            $bmi = $answers->weight / ($answers->height * $answers->height);
        }

        if (!empty($answers->weight)) {
            $water = $answers->weight * 0.03;
            $protein = $answers->weight * 1.75;
        }

        if (!empty($answers->weight) && !empty($answers->height) && !empty($answers->age) && !empty($answers->sex)) {
            if ($answers->sex === 'male') {
                $bmr = 10 * $answers->weight + 6.25 * $answers->height - 5 * $answers->age + 5;
            } else {
                $bmr = 10 * $answers->weight + 6.25 * $answers->height - 5 * $answers->age - 161;
            }
        }

        return response()->json([
            'bmi' => $bmi !== null ? round($bmi, 2) : null,
            'water' => $water !== null ? round($water, 2) : null,
            'protein' => $protein !== null ? round($protein, 2) : null,
            'bmr' => $bmr !== null ? round($bmr, 2) : null,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'height' => $answers->height,
                'weight' => $answers->weight,
                'fitness_goal' => $answers->fitness_goal
            ],
        ]);
    }
}
