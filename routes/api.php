<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SocialAccountController;
use App\Http\Controllers\Api\TagController;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Posts routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    
    // Post status management routes
    Route::put('/posts/{post}/status', [PostController::class, 'changeStatus']);
    Route::put('/posts/{post}/schedule', [PostController::class, 'updateSchedule']);
    Route::post('/posts/{post}/duplicate', [PostController::class, 'duplicate']);

    // Social Accounts routes
    Route::get('/social-accounts', [SocialAccountController::class, 'index']);
    Route::post('/social-accounts', [SocialAccountController::class, 'store']);
    Route::get('/social-accounts/{socialAccount}', [SocialAccountController::class, 'show']);
    Route::put('/social-accounts/{socialAccount}', [SocialAccountController::class, 'update']);
    Route::delete('/social-accounts/{socialAccount}', [SocialAccountController::class, 'destroy']);

    // Tags routes
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::get('/tags/{tag}', [TagController::class, 'show']);
    Route::put('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
}); 


