<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
});

Route::post('/logout', [WebAuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard']);
    Route::get('/forms', [PageController::class, 'formsIndex']);
    Route::get('/forms/create', [PageController::class, 'formsCreate']);
    Route::get('/forms/{form}', [PageController::class, 'formsShow'])->whereNumber('form');
    Route::get('/forms/{form}/edit', [PageController::class, 'formsEdit'])->whereNumber('form');
    Route::get('/forms/{form}/analytics', [PageController::class, 'formsAnalytics'])->whereNumber('form');
    Route::get('/forms/{form}/submissions', [PageController::class, 'submissionsIndex'])->whereNumber('form');
    Route::get('/forms/{form}/submissions/{submission}', [PageController::class, 'submissionsShow'])->whereNumber('form', 'submission');
    Route::get('/users', [PageController::class, 'usersIndex'])->middleware('role:super_admin');
    Route::get('/users/{user}', [PageController::class, 'usersShow'])->middleware('role:super_admin')->whereNumber('user');
    Route::get('/change-password', [PageController::class, 'changePassword']);
});

Route::get('/form/{slug}', [PageController::class, 'publicForm']);

Route::get('/', fn () => redirect('/login'));
