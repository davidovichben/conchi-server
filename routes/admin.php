<?php

use App\Http\Controllers\Admin\InteractionCategoryController;
use App\Http\Controllers\Admin\ProgramDayController;
use App\Http\Controllers\Admin\ProgramWeekController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InteractionController;
use Illuminate\Support\Facades\Route;

Route::post('/users/search', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::get('/users/{userId}/programWeeks', [UserController::class, 'programWeeks']);
Route::get('/users/{userId}/programDays', [UserController::class, 'programDays']);

Route::post('/translations/search', [TranslationController::class, 'index']);
Route::resource('/translations', TranslationController::class)->except('index');

Route::post('/interactions/search', [InteractionController::class, 'index']);
Route::get('/interactions/select', [InteractionController::class, 'select']);
Route::resource('/interactions', InteractionController::class)->except('index');

Route::post('/interactionCategories/search', [InteractionCategoryController::class, 'index']);
Route::get('/interactionCategories/select', [InteractionCategoryController::class, 'select']);
Route::resource('/interactionCategories', InteractionCategoryController::class)->except('index');

Route::resource('/weeks', ProgramWeekController::class);

Route::post('/days', [ProgramDayController::class, 'store']);
Route::delete('/days/{programDay}', [ProgramDayController::class, 'destroy']);
Route::post('/days/{dayId}/interaction', [ProgramDayController::class, 'storeInteraction']);
Route::delete('/days/{dayId}/interaction', [ProgramDayController::class, 'deleteInteraction']);
