<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SocialAccountController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TeamController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/social-accounts/{socialAccount}/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/social-accounts/{socialAccount}/posts/{post}', [PostController::class, 'show']);
    Route::put('/social-accounts/{socialAccount}/posts/{post}', [PostController::class, 'update']);
    Route::delete('/social-accounts/{socialAccount}/posts/{post}', [PostController::class, 'destroy']);

    Route::put('/social-accounts/{socialAccount}/posts/{post}/status', [PostController::class, 'changeStatus']);
    Route::put('/social-accounts/{socialAccount}/posts/{post}/schedule', [PostController::class, 'updateSchedule']);
    Route::post('/social-accounts/{socialAccount}/posts/{post}/duplicate', [PostController::class, 'duplicate']);

    Route::get('/profiles/{profile}/social-accounts', [SocialAccountController::class, 'index']);
    Route::post('/profiles/{profile}/social-accounts', [SocialAccountController::class, 'store']);
    Route::get('/profiles/{profile}/social-accounts/{socialAccount}', [SocialAccountController::class, 'show']);
    Route::put('/profiles/{profile}/social-accounts/{socialAccount}', [SocialAccountController::class, 'update']);
    Route::delete('/profiles/{profile}/social-accounts/{socialAccount}', [SocialAccountController::class, 'destroy']);

    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::get('/tags/{tag}', [TagController::class, 'show']);
    Route::put('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

    Route::get('/teams', [TeamController::class, 'index']);
}); 


