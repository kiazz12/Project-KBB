<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WebAuthController;
use App\Models\SubmissionData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/activity', [PageController::class, 'activity'])->name('dashboard.activity');
    Route::get('/forms', [PageController::class, 'formsIndex'])->name('forms.index');
    Route::get('/forms/create', [PageController::class, 'formsCreate'])->name('forms.create');
    Route::post('/forms', [PageController::class, 'formsStore'])->name('forms.store');
    Route::get('/forms/{form}', [PageController::class, 'formsShow'])->name('forms.show')->whereNumber('form');
    Route::get('/forms/{form}/edit', [PageController::class, 'formsEdit'])->name('forms.edit')->whereNumber('form');
    Route::put('/forms/{form}/settings', [PageController::class, 'updateSettings'])->name('forms.settings.update')->whereNumber('form');
    Route::post('/forms/{form}/publish', [PageController::class, 'publishForm'])->name('forms.publish')->whereNumber('form');
    Route::post('/forms/{form}/close', [PageController::class, 'closeForm'])->name('forms.close')->whereNumber('form');

    Route::post('/forms/{form}/fields', [PageController::class, 'storeField'])->name('forms.fields.store')->whereNumber('form');
    Route::put('/forms/{form}/fields/{field}', [PageController::class, 'updateField'])->name('forms.fields.update')->whereNumber('form')->whereNumber('field');
    Route::delete('/forms/{form}/fields/{field}', [PageController::class, 'deleteField'])->name('forms.fields.delete')->whereNumber('form')->whereNumber('field');
    Route::post('/forms/{form}/fields/reorder', [PageController::class, 'reorderFields'])->name('forms.fields.reorder')->whereNumber('form');
    Route::post('/forms/{form}/fields/{field}/clone', [PageController::class, 'cloneField'])->name('forms.fields.clone')->whereNumber('form')->whereNumber('field');

    Route::post('/forms/{form}/sections', [PageController::class, 'storeSection'])->name('forms.sections.store')->whereNumber('form');
    Route::put('/forms/{form}/sections/{section}', [PageController::class, 'updateSection'])->name('forms.sections.update')->whereNumber('form')->whereNumber('section');
    Route::delete('/forms/{form}/sections/{section}', [PageController::class, 'deleteSection'])->name('forms.sections.delete')->whereNumber('form')->whereNumber('section');
    Route::post('/forms/{form}/sections/reorder', [PageController::class, 'reorderSections'])->name('forms.sections.reorder')->whereNumber('form');

    Route::post('/forms/{form}/duplicate', [PageController::class, 'duplicateForm'])->name('forms.duplicate')->whereNumber('form');
    Route::get('/forms/{form}/analytics', [PageController::class, 'formsAnalytics'])->name('forms.analytics')->whereNumber('form');
    Route::get('/forms/{form}/submissions', [PageController::class, 'submissionsIndex'])->name('forms.submissions.index')->whereNumber('form');
    Route::get('/forms/{form}/submissions/{submission}', [PageController::class, 'submissionsShow'])->name('forms.submissions.show')->whereNumber('form')->whereNumber('submission');
    Route::post('/forms/{form}/submissions/{submission}/delete', [PageController::class, 'deleteSubmission'])->name('forms.submissions.delete')->whereNumber('form')->whereNumber('submission');
    Route::post('/forms/{form}/submissions/bulk-delete', [PageController::class, 'bulkDeleteSubmissions'])->name('forms.submissions.bulk-delete')->whereNumber('form');
    Route::get('/forms/{form}/export/csv', [PageController::class, 'exportCsv'])->name('forms.export.csv')->whereNumber('form');
    Route::get('/forms/{form}/export/xlsx', [PageController::class, 'exportXlsx'])->name('forms.export.xlsx')->whereNumber('form');
    Route::get('/forms/{form}/export/pdf', [PageController::class, 'exportPdf'])->name('forms.export.pdf')->whereNumber('form');
    Route::get('/users', [PageController::class, 'usersIndex'])->name('users.index')->middleware('role:super_admin');
    Route::get('/users/{user}', [PageController::class, 'usersShow'])->name('users.show')->middleware('role:super_admin')->whereNumber('user');
    Route::get('/change-password', [PageController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [WebAuthController::class, 'changePassword']);

    Route::post('/session/ping', [PageController::class, 'sessionPing'])->name('session.ping');
    Route::post('/session/extend', [PageController::class, 'sessionExtend'])->name('session.extend');
    Route::post('/session/display-name', [PageController::class, 'sessionDisplayName'])->name('session.display-name');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllRead');
    });
});

Route::get('/form/{slug}', [PageController::class, 'publicForm'])->name('public-form');
Route::post('/form/{slug}', [PageController::class, 'submitPublicForm'])->name('public-form.submit')->middleware('throttle:15,1');

Route::get('/uploads/{path}', function (string $path) {
    abort_unless(Auth::check(), 401);

    $valueInDb = 'uploads/'.$path;
    $submissionData = SubmissionData::where('value', $valueInDb)->first();
    abort_if(! $submissionData, 404);

    $fullPath = storage_path('app/private/'.$valueInDb);
    abort_if(! file_exists($fullPath), 404);

    $form = $submissionData->submission->form;
    /** @var User $user */
    $user = Auth::user();
    abort_if(Auth::id() !== $form->user_id && ! $user->isSuperAdmin(), 403);

    return response()->file($fullPath);
})->where('path', '.*')->name('uploads.show');

Route::get('/', fn () => redirect('/login'));
