<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
