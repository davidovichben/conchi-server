<?php

use App\Http\Controllers\GeneralController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailsController;
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

Route::get('/translations', [GeneralController::class, 'translations']);
Route::post('/user', [UserController::class, 'store']);
Route::post('/user/login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/options', [GeneralController::class, 'options']);
    Route::get('/hobbies', [GeneralController::class, 'hobbies']);
    Route::get('/sentences', [GeneralController::class, 'sentences']);

    Route::get('/user/details', [UserDetailsController::class, 'show']);
    Route::put('/user/details', [UserDetailsController::class, 'put']);
});
