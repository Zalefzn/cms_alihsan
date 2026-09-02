<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/panduan', function () {
    return view('panduan');
})->name('panduan');
