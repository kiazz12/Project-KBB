<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'loginForm'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post')->middleware('throttle:5,1');

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/activity', [DashboardController::class, 'activity'])->name('admin.activity');
    Route::resource('/users', UserController::class, ['as' => 'admin'])->except(['show']);
    Route::get('/forms', [FormController::class, 'index'])->name('admin.forms.index');
    Route::get('/forms/{form}', [FormController::class, 'show'])->name('admin.forms.show');
});
