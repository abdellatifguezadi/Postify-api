<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SocialAccountController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskColumnController;
use App\Http\Controllers\Api\TaskController;
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
    Route::delete('/social-accounts/{socialAccount}', [SocialAccountController::class, 'destroy']);

    // Tags routes
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

    // Tasks routes
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);


    // Teams routes
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']); 
    Route::put('/teams/{team}', [TeamController::class, 'update']);
    
    // Invites routes
    Route::get('/invites', [InviteController::class, 'index']);
    Route::post('/teams/{team}/invite', [InviteController::class, 'inviteUser']);
    Route::post('/invites/{invite}/accept', [InviteController::class, 'acceptInvite']);
    Route::post('/invites/{invite}/reject', [InviteController::class, 'rejectInvite']);

    Route::put('/tasks/{task}/status', [TaskController::class, 'changeStatus']);
    Route::put('/tasks/{task}/assigntoUsers/', [TaskController::class, 'assignToUsers']);
    Route::put('/tasks/{task}/unassignFromUsers/', [TaskController::class, 'unassignFromUsers']);
    Route::get('/tasks/{task}/getUsers', [TaskController::class, 'getUsers']);
    Route::get('/tasks/{task}/getTaskColumn', [TaskController::class, 'getTaskColumn']);


    // Profile routes
    Route::get('/teams/{team}/profiles', [ProfileController::class, 'index']);
    Route::post('/teams/{team}/profiles/', [ProfileController::class, 'store']);
    Route::get('/profiles/{profile}', [ProfileController::class, 'show']);
    Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy']);
    Route::get('/profiles/{profile}/social-accounts', [ProfileController::class, 'getSocialAccounts']);
    Route::get('/profiles/{profile}/columns', [ProfileController::class, 'getColumns']);

    // Task Columns routes
    // Route::get('/profiles/{profile}/columns', [TaskColumnController::class, 'index']);
    Route::post('/profiles/{profile}/columns', [TaskColumnController::class, 'store']);
    Route::get('profiles/{profile}/columns/{taskColumn}', [TaskColumnController::class, 'show']);
    Route::put('profiles/{profile}/columns/{taskColumn}', [TaskColumnController::class, 'update']);
    Route::delete('profiles/{profile}/columns/{taskColumn}', [TaskColumnController::class, 'destroy']);
});
