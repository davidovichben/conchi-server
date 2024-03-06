<?php

use App\Http\Controllers\GeneralController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\PaymentPackageController;
use App\Http\Controllers\ProgramDayController;
use App\Http\Controllers\ProgramWeekController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailsController;
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

Route::get('/translations', [GeneralController::class, 'translations']);
Route::post('/user', [UserController::class, 'store']);
Route::post('/user/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/options', [GeneralController::class, 'options']);
    Route::get('/hobbies', [GeneralController::class, 'hobbies']);
    Route::get('/sentences', [GeneralController::class, 'sentences']);

    Route::get('/user/details', [UserDetailsController::class, 'show']);
    Route::post('/user/details', [UserDetailsController::class, 'update']);
    Route::put('/user/hobbies', [UserDetailsController::class, 'updateHobbies']);
    Route::put('/user/sentences', [UserDetailsController::class, 'updateSentences']);

    Route::get('/programWeek', [ProgramWeekController::class, 'index']);
    Route::get('/paymentPackages', [PaymentPackageController::class, 'index']);

    Route::middleware('paid')->group(function () {
        Route::get('/programWeek/{weekId}/days', [ProgramWeekController::class, 'days']);
        Route::get('/programWeek/{weekId}/report', [ProgramWeekController::class, 'report']);
        Route::put('/programWeek/{weekId}/report', [ProgramWeekController::class, 'updateReport']);
        Route::get('/programDay/{programDay}', [ProgramDayController::class, 'show']);
        Route::put('/programDay/{programDay}/complete', [ProgramDayController::class, 'complete']);
        Route::get('/interactions/categories', [InteractionController::class, 'categories']);
        Route::get('/interactions/{category}/byCategory', [InteractionController::class, 'byCategory']);
        Route::put('/interactions/{interactionId}/like', [InteractionController::class, 'like']);
        Route::put('/interactions/{interactionId}/status', [InteractionController::class, 'setStatus']);
    });
});
