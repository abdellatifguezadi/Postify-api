<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OAuthController;

Route::get('/', function () {
    return view('welcome');
});

// OAuth callback routes (moved from api.php to have session support)
Route::get('/api/facebook/callback', [OAuthController::class, 'handleFacebookCallback']);
Route::get('/api/oauth/callback-data', [OAuthController::class, 'getOAuthCallbackData']);
