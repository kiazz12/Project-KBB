<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Auth;
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
    Route::get('/forms/{form}/submissions/{submission}', [PageController::class, 'submissionsShow'])->name('forms.submissions.show')->whereNumber('form')->whereNumber('submission');
    Route::post('/forms/{form}/submissions/{submission}/delete', [PageController::class, 'deleteSubmission'])->name('forms.submissions.delete')->whereNumber('form')->whereNumber('submission');
    Route::get('/forms/{form}/export/csv', [PageController::class, 'exportCsv'])->name('forms.export.csv')->whereNumber('form');
    Route::get('/forms/{form}/export/pdf', [PageController::class, 'exportPdf'])->name('forms.export.pdf')->whereNumber('form');
    Route::get('/forms/{form}/export/uang-saku', [PageController::class, 'exportUangSakuPdf'])->name('forms.export.uang-saku')->whereNumber('form');
    Route::get('/forms/{form}/export/presensi', [PageController::class, 'exportPresensiPdf'])->name('forms.export.presensi')->whereNumber('form');
    Route::get('/users', [PageController::class, 'usersIndex'])->name('users.index')->middleware('role:super_admin');
    Route::get('/users/{user}', [PageController::class, 'usersShow'])->name('users.show')->middleware('role:super_admin')->whereNumber('user');
    Route::get('/change-password', [PageController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [WebAuthController::class, 'changePassword']);

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('unread');
        Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('markAllRead');
    });
});

Route::get('/form/{slug}', [PageController::class, 'publicForm'])->name('public-form');

Route::get('/uploads/{path}', function (string $path) {
    abort_unless(Auth::check(), 401);

    $valueInDb = 'uploads/' . $path;
    $submissionData = \App\Models\SubmissionData::where('value', $valueInDb)->first();
    abort_if(!$submissionData, 404);

    $fullPath = storage_path('app/private/' . $valueInDb);
    abort_if(!file_exists($fullPath), 404);

    $form = $submissionData->submission->form;
    /** @var \App\Models\User $user */
    $user = Auth::user();
    abort_if(Auth::id() !== $form->user_id && !$user->isSuperAdmin(), 403);

    return response()->file($fullPath);
})->where('path', '.*')->name('uploads.show');

Route::get('/', fn() => redirect('/login'));
