<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\FormSubmissionController;
use App\Http\Controllers\Api\FormComponentController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FormAnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public forms
    Route::get('/forms/public', [FormController::class, 'publicForms']);
    Route::get('/forms/{id}/public', [FormController::class, 'show']);

    // Public form submission
    Route::post('/forms/{formId}/submit', [FormSubmissionController::class, 'submit']);

    // Form components (public access for form builder)
    Route::get('/components', [FormComponentController::class, 'index']);
    Route::get('/components/{id}', [FormComponentController::class, 'show']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Forms management
    Route::apiResource('forms', FormController::class);

    // Form analytics
    Route::get('/forms/{formId}/analytics', [FormAnalyticsController::class, 'getFormAnalytics']);

    // Form submissions management
    Route::apiResource('submissions', FormSubmissionController::class);
    Route::get('/forms/{formId}/submissions', [FormSubmissionController::class, 'index']);

    // Form components management (admin only)
    Route::apiResource('components', FormComponentController::class)->except(['index', 'show']);

    // Admin routes (admin only)
    Route::prefix('admin')->group(function () {
        Route::get('/statistics', [AdminController::class, 'statistics']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::put('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword']);
        Route::get('/forms', [AdminController::class, 'allForms']);
        Route::get('/submissions', [AdminController::class, 'allSubmissions']);
        Route::get('/users', [AdminController::class, 'users']);
    });
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
    ]);
});
