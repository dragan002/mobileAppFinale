<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompletionController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\ReflectionController;
use App\Http\Controllers\Api\ResetController;
use App\Http\Controllers\Api\SetupController;
use App\Http\Controllers\Api\StateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::prefix('api')->group(function () {
    Route::get('/state', [StateController::class, 'index']);
    Route::get('/analytics', [AnalyticsController::class, 'index']);
    Route::post('/setup', [SetupController::class, 'store']);
    Route::post('/habits', [HabitController::class, 'store']);
    Route::put('/habits/{habit}', [HabitController::class, 'update']);
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::post('/completions/toggle', [CompletionController::class, 'toggle']);
    Route::post('/reflections', [ReflectionController::class, 'store']);
    Route::delete('/reset', [ResetController::class, 'destroy']);
});
