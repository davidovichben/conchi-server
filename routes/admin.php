<?php

use App\Http\Controllers\Admin\TranslationController;
use Illuminate\Support\Facades\Route;

Route::post('/translations/search', [TranslationController::class, 'index']);
Route::resource('/translations', TranslationController::class)->except('index');

