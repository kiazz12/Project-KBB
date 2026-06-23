<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\FieldController;
use App\Http\Controllers\API\FormCrudController;
use App\Http\Controllers\API\SubmissionApiController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/v1/forms/public/{slug}', [SubmissionApiController::class, 'showPublic']);
Route::post('/v1/forms/public/{slug}', [SubmissionApiController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/auth/logout', [AuthController::class, 'logout']);
    Route::get('/v1/auth/me', [AuthController::class, 'me']);
    Route::post('/v1/auth/change-password', [AuthController::class, 'changePassword']);

    Route::get('/v1/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/v1/dashboard/recent-forms', [DashboardController::class, 'recentForms']);

    Route::get('/v1/forms', [FormCrudController::class, 'index']);
    Route::post('/v1/forms', [FormCrudController::class, 'store']);
    Route::get('/v1/forms/{form}', [FormCrudController::class, 'show'])->whereNumber('form');
    Route::put('/v1/forms/{form}', [FormCrudController::class, 'update'])->whereNumber('form');
    Route::delete('/v1/forms/{form}', [FormCrudController::class, 'destroy'])->whereNumber('form');
    Route::post('/v1/forms/{form}/duplicate', [FormCrudController::class, 'duplicate'])->whereNumber('form');
    Route::post('/v1/forms/{form}/publish', [FormCrudController::class, 'publish'])->whereNumber('form');
    Route::post('/v1/forms/{form}/close', [FormCrudController::class, 'close'])->whereNumber('form');
    Route::get('/v1/forms/{form}/analytics', [FormCrudController::class, 'analytics'])->whereNumber('form');
    Route::get('/v1/forms/{form}/export/csv', [FormCrudController::class, 'exportCsv'])->whereNumber('form');

    Route::post('/v1/forms/{form}/fields', [FieldController::class, 'store'])->whereNumber('form');
    Route::put('/v1/forms/{form}/fields/{field}', [FieldController::class, 'update'])->whereNumber('form');
    Route::delete('/v1/forms/{form}/fields/{field}', [FieldController::class, 'destroy'])->whereNumber('form');
    Route::post('/v1/forms/{form}/fields/reorder', [FieldController::class, 'reorder'])->whereNumber('form');

    Route::get('/v1/forms/{form}/submissions', [SubmissionApiController::class, 'index'])->whereNumber('form');
    Route::get('/v1/forms/{form}/submissions/{submission}', [SubmissionApiController::class, 'show'])->whereNumber('form');
    Route::delete('/v1/forms/{form}/submissions/{submission}', [SubmissionApiController::class, 'destroy'])->whereNumber('form');

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/v1/users', [UserController::class, 'index']);
        Route::post('/v1/users', [UserController::class, 'store']);
        Route::put('/v1/users/{user}', [UserController::class, 'update']);
        Route::delete('/v1/users/{user}', [UserController::class, 'destroy']);
    });
});
