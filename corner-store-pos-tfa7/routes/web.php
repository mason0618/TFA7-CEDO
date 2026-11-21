<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'edit', 'update']);
Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

Route::get('/storage/avatars/{filename}', [UserController::class, 'avatar'])
    ->where('filename', '[a-f0-9-]{36}\.jpg')
    ->name('users.avatar');
