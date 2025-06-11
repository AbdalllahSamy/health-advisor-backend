<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalorieController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'nullable|numeric',         // in kg
            'height' => 'nullable|numeric',         // in cm
            'age' => 'nullable|numeric',
            'gender' => 'nullable|in:male,female',
            'activity_level' => 'nullable|in:sedentary,moderate,active,very_active',
        ]);

        $type = $request->get('type');

        // Adjust BMR based on activity level
        switch ($type) {
            case 'BMI':
                $calories = $request->weight / ($request->height * $request->height);
                break;
            case 'water':
                $calories = $request->weight * 0.03;
                break;
            case 'protine':
                $calories = $request->weight * 1.75;
                break;
            case 'BMR':
                if($request->gender === 'male'){
                    $calories = 10 * $request->weight + 6.25 * $request->height - 5 * $request->age + 5;
                } else {
                    $calories = 10 * $request->weight + 6.25 * $request->height - 5 * $request->age - 161;
                }
                break;
            default:
                return response()->json(['error' => 'Invalid activity level'], 422);
        }

        return response()->json([
            'calc' => round($calories, 2),
        ]);
    }
}
