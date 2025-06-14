<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\User;
use App\Models\WeeklyPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $answers = Answer::where('user_id', $request->user()->id)->first();
        $week_plan = WeeklyPlan::where('user_id', $request->user()->id)->first();

        return response()->json(['token' => $token, 'is_frist_login' => $answers ? 1 : 0, 'is_gym_only' => $week_plan->type === "gym_only" ? "gym_only" : "not_gym_only"]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all() ,[
            'password' => 'required|confirmed',
            'phone' => 'requiredunique:users,email',
            'email' => 'required|unique:users,email',
            'first_name' => 'required',
            'last_name' => 'required'
        ]);
        if($validator->failed()){
            return response()->json(['erorr' => $validator->errors()]);
        }
        $user = User::create([
            'name'     => $request->first_name . " " . $request->last_name,
            'email'    => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['token' => $token]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout(); // Invalidates the token

        return response()->json(['message' => 'Successfully logged out']);
    }
}
