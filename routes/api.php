<?php

use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\NewsletterSubscriptionController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/pages', [PageController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::get('/menu', [MenuController::class, 'index']);
Route::get('/settings', [SettingController::class, 'show']);
Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])
    ->middleware('throttle:5,1');
Route::post('/ppdb', [RegistrationController::class, 'store'])
    ->middleware('throttle:5,1');
