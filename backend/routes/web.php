<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
});

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/forms', [PageController::class, 'formsIndex'])->name('forms.index');
    Route::get('/forms/create', [PageController::class, 'formsCreate'])->name('forms.create');
    Route::get('/forms/{form}', [PageController::class, 'formsShow'])->name('forms.show')->whereNumber('form');
    Route::get('/forms/{form}/edit', [PageController::class, 'formsEdit'])->name('forms.edit')->whereNumber('form');
    Route::get('/forms/{form}/analytics', [PageController::class, 'formsAnalytics'])->name('forms.analytics')->whereNumber('form');
    Route::get('/forms/{form}/submissions', [PageController::class, 'submissionsIndex'])->name('forms.submissions.index')->whereNumber('form');
    Route::get('/forms/{form}/submissions/{submission}', [PageController::class, 'submissionsShow'])->name('forms.submissions.show')->whereNumber('form', 'submission');
    Route::post('/forms/{form}/submissions/{submission}/delete', [PageController::class, 'deleteSubmission'])->name('forms.submissions.delete')->whereNumber('form', 'submission');
    Route::get('/forms/{form}/export/csv', [PageController::class, 'exportCsv'])->name('forms.export.csv')->whereNumber('form');
    Route::get('/forms/{form}/export/pdf', [PageController::class, 'exportPdf'])->name('forms.export.pdf')->whereNumber('form');
    Route::get('/users', [PageController::class, 'usersIndex'])->name('users.index')->middleware('role:super_admin');
    Route::get('/users/{user}', [PageController::class, 'usersShow'])->name('users.show')->middleware('role:super_admin')->whereNumber('user');
    Route::get('/change-password', [PageController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [WebAuthController::class, 'changePassword']);
});

Route::get('/form/{slug}', [PageController::class, 'publicForm'])->name('public-form');

Route::get('/', fn() => redirect('/login'));
