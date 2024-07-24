<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ContentPackageController;
use App\Http\Controllers\Admin\IndexController;
use App\Http\Controllers\Admin\InteractionCategoryController;
use App\Http\Controllers\Admin\InteractionSubCategoryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentPackageController;
use App\Http\Controllers\Admin\ProgramDayController;
use App\Http\Controllers\Admin\ProgramReportOptionController;
use App\Http\Controllers\Admin\ProgramReportQuestionController;
use App\Http\Controllers\Admin\ProgramWeekController;
use App\Http\Controllers\Admin\RatingsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InteractionController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::post('/login', [IndexController::class, 'login']);

Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    Route::post('/users/search', [UserController::class, 'index']);
    Route::put('/users/{user}/upload', [UserController::class, 'upload']);
    Route::delete('/users/{user}/deleteFile', [UserController::class, 'deleteFile']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/users/{userId}/programWeeks', [UserController::class, 'programWeeks']);
    Route::get('/users/{userId}/programDays', [UserController::class, 'programDays']);
    Route::put('/users/{user}/activate', [UserController::class, 'activate']);

    Route::post('/translations/search', [TranslationController::class, 'index']);
    Route::put('/translations/{translation}', [TranslationController::class, 'update']);

    Route::post('/media/search', [MediaController::class, 'index']);
    Route::resource('media', MediaController::class)->except('index');

    Route::post('/interactions/search', [InteractionController::class, 'index']);
    Route::get('/interactions/select', [InteractionController::class, 'select']);
    Route::post('/interactions/{interactionId}/audioFiles', [InteractionController::class, 'audioFiles']);
    Route::delete('/audioFiles/{audioFile}', [InteractionController::class, 'destroyAudioFile']);

    Route::resource('/interactions', InteractionController::class)->except('index');

    Route::post('/interactionCategories/search', [InteractionCategoryController::class, 'index']);
    Route::get('/interactionCategories/select', [InteractionCategoryController::class, 'select']);
    Route::resource('/interactionCategories', InteractionCategoryController::class)->except('index');

    Route::post('/interactionSubCategories/search', [InteractionSubCategoryController::class, 'index']);
    Route::get('/interactionSubCategories/select', [InteractionSubCategoryController::class, 'select']);
    Route::resource('/interactionSubCategories', InteractionSubCategoryController::class)->except('index');

    Route::resource('/weeks', ProgramWeekController::class);
    Route::put('/weeks/{programWeek}/activate', [ProgramWeekController::class, 'activate']);

    Route::post('/days', [ProgramDayController::class, 'store']);
    Route::put('/days/{programDay}', [ProgramDayController::class, 'update']);
    Route::delete('/days/{programDay}', [ProgramDayController::class, 'destroy']);
    Route::post('/days/{dayId}/activity', [ProgramDayController::class, 'storeActivity']);
    Route::delete('/days/{dayId}/activity', [ProgramDayController::class, 'deleteActivity']);

    Route::resource('/reportQuestions', ProgramReportQuestionController::class);
    Route::resource('/reportOptions', ProgramReportOptionController::class);

    Route::post('/articles/search', [ArticleController::class, 'index']);
    Route::resource('/articles', ArticleController::class)->except('index');

    Route::post('/paymentPackages/search', [PaymentPackageController::class, 'index']);
    Route::resource('/paymentPackages', PaymentPackageController::class)->except('index');

    Route::post('/contentPackages/search', [ContentPackageController::class, 'index']);
    Route::resource('/contentPackages', ContentPackageController::class)->except('index');

    Route::post('/pages/search', [PageController::class, 'index']);
    Route::put('/pages/{page}', [PageController::class, 'update']);

    Route::post('/ratings/search', [RatingsController::class, 'index']);
    Route::resource('/ratings', RatingsController::class)->except('index');

    Route::get('/reports', [ReportController::class, 'index']);
});

