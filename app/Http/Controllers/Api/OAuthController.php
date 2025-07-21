<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Profile;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OAuthController extends Controller
{
    /**
     * Get Facebook login URL with state parameter (more secure)
     */
    public function getFacebookLoginUrlWithState(Profile $profile)
    {
        $state = base64_encode(json_encode(['profile_id' => $profile->id, 'platform' => 'facebook']));

        // Generate Facebook OAuth URL manually
        $params = [
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect'),
            'scope' => 'email,public_profile,pages_manage_posts,pages_read_engagement',
            'response_type' => 'code',
            'state' => $state
        ];

        $redirectUrl = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);

        return response()->json([
            'status' => 'success',
            'login_url' => $redirectUrl,
            'profile_id' => $profile->id,
            'state' => $state
        ]);
    }

    /**
     * Get Instagram login URL with state parameter
     */
    public function getInstagramLoginUrlWithState(Profile $profile)
    {
        $state = base64_encode(json_encode(['profile_id' => $profile->id, 'platform' => 'instagram']));

        // Generate Facebook OAuth URL manually (Instagram uses Facebook OAuth)
        $params = [
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect'),
            'scope' => 'email,public_profile,pages_manage_posts,pages_read_engagement,instagram_basic,instagram_content_publish',
            'response_type' => 'code',
            'state' => $state
        ];

        $redirectUrl = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);

        return response()->json([
            'status' => 'success',
            'login_url' => $redirectUrl,
            'profile_id' => $profile->id,
            'state' => $state
        ]);
    }

    /**
     * Handle Facebook OAuth callback (works for both Facebook and Instagram)
     */
    public function handleFacebookCallback(Request $request, $platform = 'facebook')
    {
        try {
            // For now, let's not require a specific profile and use a default one
            $profile = Profile::first();
            if (!$profile) {
                throw new \Exception('No profiles found in the system. Please create a profile first.');
            }

            // Get the authorization code from the callback
            $code = $request->get('code');
            if (!$code) {
                throw new \Exception('No authorization code received from Facebook');
            }

            // Exchange code for access token manually
            $tokenResponse = Http::post('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri' => config('services.facebook.redirect'),
                'code' => $code
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Failed to exchange code for token: ' . $tokenResponse->body());
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                throw new \Exception('No access token received from Facebook');
            }

            // Get user information from Facebook API
            $userInfo = $this->getFacebookUserInfo($accessToken);

            // Get pages for the user
            $pages = $this->getUserPages($accessToken, $platform);

            // Prepare the data that will be sent to frontend
            $callbackData = [
                'status' => 'success',
                'message' => 'Authentication successful. Please select a page to connect.',
                'data' => [
                    'user_token' => $accessToken,
                    'user_name' => $userInfo['name'] ?? 'Unknown User',
                    'user_avatar' => $userInfo['picture']['data']['url'] ?? null,
                    'platform' => $platform,
                    'pages' => $pages,
                    'profile_id' => $profile->id
                ]
            ];

            // Generate a unique session key for this callback data
            $sessionKey = 'oauth_callback_' . uniqid();

            // Store the callback data in cache/session for the frontend to retrieve
            // Using cache for 10 minutes
            cache()->put($sessionKey, $callbackData, 600);

            // Build the frontend redirect URL with the session key
            $frontendUrl = config('services.frontend.url') . config('services.frontend.oauth_callback_path');
            $redirectUrl = $frontendUrl . '?' . http_build_query([
                'session_key' => $sessionKey,
                'platform' => $platform,
                'status' => 'success'
            ]);

            // Redirect to frontend
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('OAuth callback failed:', ['error' => $e->getMessage()]);

            // Generate error session key
            $errorSessionKey = 'oauth_callback_' . uniqid();
            $errorData = [
                'status' => 'error',
                'message' => 'Authentication failed: ' . $e->getMessage(),
                'data' => null
            ];

            cache()->put($errorSessionKey, $errorData, 600);

            // Redirect to frontend with error
            $frontendUrl = config('services.frontend.url') . config('services.frontend.oauth_callback_path');
            $redirectUrl = $frontendUrl . '?' . http_build_query([
                'session_key' => $errorSessionKey,
                'platform' => $platform,
                'status' => 'error'
            ]);

            return redirect($redirectUrl);
        }
    }
    /**
     * Get user's Facebook pages or Instagram Business Accounts
     */
    private function getUserPages($userToken, $platform)
    {
        try {
            $pages = $this->getFacebookPages($userToken);

            if ($platform === 'instagram') {
                return $this->getInstagramBusinessAccounts($pages, $userToken);
            }

            return $pages;
        } catch (\Exception $e) {
            Log::error('Failed to get user pages:', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetches pages from Facebook Graph API.
     */
    private function getFacebookPages($userToken)
    {
        $version = config('services.facebook.graph_version', 'v18.0');
        $response = Http::get("https://graph.facebook.com/{$version}/me/accounts", [
            'access_token' => $userToken,
            'fields' => 'id,name,access_token,category,fan_count'
        ]);

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        return [];
    }

    /**
     * Filters pages for Instagram Business Accounts and fetches their details.
     */
    private function getInstagramBusinessAccounts(array $pages, $userToken)
    {
        $instagramPages = [];
        $version = config('services.facebook.graph_version', 'v18.0');

        foreach ($pages as $page) {
            $response = Http::get("https://graph.facebook.com/{$version}/{$page['id']}", [
                'access_token' => $userToken,
                'fields' => 'instagram_business_account'
            ]);

            if (!$response->successful()) {
                continue;
            }

            $pageData = $response->json();
            if (!isset($pageData['instagram_business_account'])) {
                continue;
            }

            $instagramAccountId = $pageData['instagram_business_account']['id'];
            $instagramAccountDetails = $this->getInstagramAccountDetails($instagramAccountId, $page['access_token']);

            if ($instagramAccountDetails) {
                $instagramPages[] = array_merge($page, [
                    'instagram_business_account_id' => $instagramAccountId,
                    'instagram_username' => $instagramAccountDetails['username'] ?? $page['name'],
                    'instagram_name' => $instagramAccountDetails['name'] ?? $page['name'],
                    'instagram_followers_count' => $instagramAccountDetails['followers_count'] ?? 0,
                    'instagram_media_count' => $instagramAccountDetails['media_count'] ?? 0,
                    'instagram_profile_picture_url' => $instagramAccountDetails['profile_picture_url'] ?? null
                ]);
            }
        }

        return $instagramPages;
    }

    /**
     * Fetches details for a given Instagram Business Account.
     */
    private function getInstagramAccountDetails($instagramAccountId, $pageToken)
    {
        $version = config('services.facebook.graph_version', 'v18.0');
        $response = Http::get("https://graph.facebook.com/{$version}/{$instagramAccountId}", [
            'access_token' => $pageToken,
            'fields' => 'id,username,name,profile_picture_url,followers_count,media_count'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }


    /**
     * Connect a specific page to the profile
     */
    public function connectPage(Request $request, Profile $profile)
    {
        try {
            $validated = $request->validate([
                'platform' => 'required|string|in:facebook,instagram',
                'page_name' => 'required|string',
                'page_access_token' => 'required|string',
                'user_token' => 'required|string',
                'instagram_business_account_id' => 'nullable|string',
                'instagram_username' => 'nullable|string'
            ]);

            // Use the profile from the URL parameter
            $profileId = $profile->id;

            // Get user information from Facebook API using the user-level token
            $userInfo = $this->getFacebookUserInfo($validated['user_token']);

            // Determine the account name and access token based on the selected page and platform
            $accountName = $validated['page_name'];
            $accessToken = $validated['page_access_token'];

            if ($validated['platform'] === 'instagram') {
                // For Instagram, the account name should be the Instagram username if available
                if (isset($validated['instagram_username'])) {
                    $accountName = $validated['instagram_username'];
                } else {
                    // This case might occur if the username wasn't fetched, fallback to page name
                    Log::warning('Instagram username not provided for connectPage, falling back to page name.');
                }
            }

            // Prepare social account data
            $socialAccountData = [
                'platform' => $validated['platform'],
                'account_name' => $accountName,
                'access_token' => $accessToken,
                'social_id' => $userInfo['id'] ?? null,
                'email' => $userInfo['email'] ?? null,
                'avatar' => $userInfo['picture']['data']['url'] ?? null
            ];

            // Check if a social account already exists for this page/account under the given profile
            $existingAccount = $profile->socialAccounts()
                ->where('platform', $validated['platform'])
                ->where('account_name', $accountName)
                ->first();

            if ($existingAccount) {
                // Update existing account with all user info
                $existingAccount->update($socialAccountData);

                return response()->json([
                    'status' => 'success',
                    'message' => ucfirst($validated['platform']) . ' account updated successfully',
                    'data' => [
                        'id' => $existingAccount->id,
                        'profile_id' => $existingAccount->profile_id,
                        'platform' => $existingAccount->platform,
                        'account_name' => $existingAccount->account_name,
                        'access_token' => $existingAccount->access_token,
                        'avatar' => $existingAccount->avatar,
                        'email' => $existingAccount->email,
                        'social_id' => $existingAccount->social_id,
                        'refresh_token' => $existingAccount->refresh_token,
                        'expires_at' => $existingAccount->expires_at,
                        'created_at' => $existingAccount->created_at,
                        'updated_at' => $existingAccount->updated_at
                    ]
                ]);
            }

            // Create new social account with all user info
            $socialAccount = $profile->socialAccounts()->create($socialAccountData);

            return response()->json([
                'status' => 'success',
                'message' => ucfirst($validated['platform']) . ' account connected successfully',
                'data' => $socialAccount->fresh() // Return the full, fresh model data
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to connect page:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect page: ' . $e->getMessage()
            ], 500);
        }
    }

    // LINKEDIN OAUTH - SIMPLE VERSION
    public function getLinkedInUrl(Profile $profile)
    {
        // Use the EXACT same state as the working manual URL
        $state = 'test_500';

        $url = "https://www.linkedin.com/oauth/v2/authorization?" . http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.linkedin.client_id'),
            'redirect_uri' => config('services.linkedin.redirect'),
            'state' => $state,
            'scope' => 'openid profile email w_member_social'
        ]);

        return response()->json([
            'status' => 'success',
            'login_url' => $url,
            'profile_id' => $profile->id
        ]);
    }

    public function connectLinkedInWithCode(Request $request, Profile $profile)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string'
            ]);

            // Step 1: Exchange code for token - Using config values from .env
            $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'authorization_code',
                'code' => $validated['code'],
                'redirect_uri' => config('services.linkedin.redirect'),
                'client_id' => config('services.linkedin.client_id'),
                'client_secret' => config('services.linkedin.client_secret')
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token exchange failed: ' . $response->body(),
                    'sent_data' => [
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => config('services.linkedin.redirect'),
                        'client_id' => config('services.linkedin.client_id')
                    ]
                ], 400);
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];

            // Step 2: Get user info
            $userResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');

            if (!$userResponse->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to get user info: ' . $userResponse->body()
                ], 400);
            }

            $userData = $userResponse->json();

            // Step 3: Save to database
            $accountName = $userData['name'] ?? $userData['email'] ?? 'LinkedIn User';

            $socialAccount = $profile->socialAccounts()->updateOrCreate(
                ['platform' => 'linkedin', 'account_name' => $accountName],
                ['access_token' => $accessToken]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'LinkedIn connected successfully!',
                'data' => [
                    'social_account' => $socialAccount,
                    'user_info' => $userData
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('LinkedIn error:', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user information from Facebook API
     */
    private function getFacebookUserInfo($userToken)
    {
        $response = Http::get('https://graph.facebook.com/v18.0/me', [
            'access_token' => $userToken,
            'fields' => 'id,name,email,picture.type(large)'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Retrieve OAuth callback data for the frontend
     */
    public function getOAuthCallbackData(Request $request)
    {
        $sessionKey = $request->get('session_key');

        if (!$sessionKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session key is required'
            ], 400);
        }

        $callbackData = cache()->get($sessionKey);

        if (!$callbackData) {
            return response()->json([
                'status' => 'error',
                'message' => 'OAuth session expired or invalid'
            ], 404);
        }

        // Remove the data from cache after retrieving it (one-time use)
        cache()->forget($sessionKey);

        return response()->json($callbackData);
    }
}
