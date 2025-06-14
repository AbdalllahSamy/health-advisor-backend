<?php

use App\Http\Controllers\Api\Answer\AnswerController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalorieController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('faq', [SettingController::class, 'faq']);
Route::get('gallary', [SettingController::class, 'gallary']);
Route::post('calculate-calories', [CalorieController::class, 'calculate']);
Route::post('contact', [SettingController::class, 'contact']);


Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/answer-questions', [AnswerController::class, 'store']);
    Route::post('/mark-exercise-done/{planId}/{dayId}/{exerciseId}', [AnswerController::class, 'markExerciseAsDone']);
    Route::post('/weekly-plan/{planId}/day/{dayId}/feedback', [AnswerController::class, 'setDayFeedback']);
    Route::get('analysis', [CalorieController::class, 'analysis']);
    Route::get('weeks-plans', [AnswerController::class, 'plans']);
    Route::get('weeks-plans/{id}', [AnswerController::class, 'plansById']);
    Route::resource('post', PostController::class);
});
