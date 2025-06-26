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
        
        $redirectUrl = Socialite::driver('facebook')
            ->redirectUrl(config('services.facebook.redirect'))
            ->stateless()
            ->scopes([
                'email',
                'public_profile',
                'pages_manage_posts',
                'pages_read_engagement'
            ])
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

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
        
        $redirectUrl = Socialite::driver('facebook')
            ->redirectUrl(config('services.facebook.redirect'))
            ->stateless()
            ->scopes([
                'email',
                'public_profile',
                'pages_manage_posts',
                'pages_read_engagement',
                'instagram_basic',
                'instagram_content_publish'
            ])
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

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
    public function handleFacebookCallback(Request $request)
    {
        try {
            $code = $request->input('code');
            if (!$code) {
                throw new \Exception('Authorization code is missing');
            }

            // Try to get profile ID and platform from state parameter
            $state = $request->input('state');
            $profileId = null;
            $platform = 'facebook'; // default

            if ($state) {
                try {
                    $stateData = json_decode(base64_decode($state), true);
                    if (isset($stateData['profile_id'])) {
                        $profileId = $stateData['profile_id'];
                    }
                    if (isset($stateData['platform'])) {
                        $platform = $stateData['platform'];
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to decode state parameter', ['state' => $state]);
                }
            }

            // If no profile ID from state, we need to get it from the user's session or request
            if (!$profileId) {
                // For now, let's try to get the first profile of the authenticated user
                $user = Auth::user();
                if (!$user) {
                    throw new \Exception('User not authenticated and no profile ID provided');
                }

                $profile = $user->teams->first()?->profiles->first();
                if (!$profile) {
                    throw new \Exception('No profile found for user');
                }
                $profileId = $profile->id;
            }

            $profile = Profile::findOrFail($profileId);

            // Use Socialite to handle the OAuth flow
            $socialUser = Socialite::driver('facebook')
                ->redirectUrl(config('services.facebook.redirect'))
                ->stateless()
                ->user();

            // Get pages for the user
            $pages = $this->getUserPages($socialUser->token, $platform);

            return response()->json([
                'status' => 'success',
                'message' => 'Authentication successful. Please select a page to connect.',
                'data' => [
                    'user_token' => $socialUser->token,
                    'user_name' => $socialUser->getName(),
                    'profile_id' => $profileId,
                    'platform' => $platform,
                    'pages' => $pages
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook/Instagram callback error:', [
                'error' => $e->getMessage(),
                'state' => $request->input('state'),
                'code' => $request->input('code')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect account: ' . $e->getMessage(),
                'debug' => [
                    'state' => $request->input('state'),
                    'code' => $request->input('code')
                ]
            ], 500);
        }
    }

    /**
     * Get user's Facebook pages
     */
    private function getUserPages($userToken, $platform)
    {
        try {
            $response = Http::get('https://graph.facebook.com/v18.0/me/accounts', [
                'access_token' => $userToken,
                'fields' => 'id,name,access_token,category,fan_count'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $pages = $data['data'] ?? [];

                // Filter pages based on platform
                if ($platform === 'instagram') {
                    // For Instagram, we need to check if pages have Instagram Business accounts
                    $instagramPages = [];
                    foreach ($pages as $page) {
                        $instagramResponse = Http::get("https://graph.facebook.com/v18.0/{$page['id']}", [
                            'access_token' => $userToken,
                            'fields' => 'instagram_business_account'
                        ]);

                        if ($instagramResponse->successful()) {
                            $pageData = $instagramResponse->json();
                            if (isset($pageData['instagram_business_account'])) {
                                $instagramAccountId = $pageData['instagram_business_account']['id'];
                                
                                // Get Instagram Business account details and token
                                $instagramAccountResponse = Http::get("https://graph.facebook.com/v18.0/{$instagramAccountId}", [
                                    'access_token' => $page['access_token'],
                                    'fields' => 'id,username,name,profile_picture_url,followers_count,media_count'
                                ]);

                                if ($instagramAccountResponse->successful()) {
                                    $instagramData = $instagramAccountResponse->json();
                                    $instagramPages[] = [
                                        'id' => $page['id'],
                                        'name' => $page['name'],
                                        'page_access_token' => $page['access_token'],
                                        'category' => $page['category'],
                                        'fan_count' => $page['fan_count'],
                                        'instagram_business_account_id' => $instagramAccountId,
                                        'instagram_username' => $instagramData['username'] ?? $page['name'],
                                        'instagram_name' => $instagramData['name'] ?? $page['name'],
                                        'instagram_followers_count' => $instagramData['followers_count'] ?? 0,
                                        'instagram_media_count' => $instagramData['media_count'] ?? 0,
                                        'instagram_profile_picture_url' => $instagramData['profile_picture_url'] ?? null
                                    ];
                                }
                            }
                        }
                    }
                    return $instagramPages;
                }

                return $pages;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get user pages:', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Connect a specific page to the profile
     */
    public function connectPage(Request $request, Profile $profile)
    {
        try {
            $validated = $request->validate([
                'platform' => 'required|string|in:facebook,instagram',
                'page_id' => 'required|string',
                'page_name' => 'required|string',
                'page_access_token' => 'required|string',
                'user_token' => 'required|string',
                'instagram_business_account_id' => 'nullable|string',
                'instagram_username' => 'nullable|string'
            ]);

            // Use the profile from the URL parameter instead of request body
            $profileId = $profile->id;

            // Determine the account name and access token based on platform
            $accountName = $validated['page_name'];
            $accessToken = $validated['page_access_token'];

            if ($validated['platform'] === 'facebook') {
                // For Facebook: use page name and page access token
                $accountName = $validated['page_name']; // "Postify"
                $accessToken = $validated['page_access_token'];
            } 
            elseif ($validated['platform'] === 'instagram') {
                // For Instagram: use Instagram username and page access token
                if (isset($validated['instagram_username'])) {
                    $accountName = $validated['instagram_username']; // "postify16"
                    $accessToken = $validated['page_access_token']; // This token works for Instagram Business
                } else {
                    throw new \Exception('Instagram username is required for Instagram platform');
                }
            }

            // Check if social account already exists for this page/account
            $existingAccount = $profile->socialAccounts()
                ->where('platform', $validated['platform'])
                ->where('account_name', $accountName)
                ->first();

            if ($existingAccount) {
                // Update existing account
                $existingAccount->update([
                    'access_token' => $accessToken
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => ucfirst($validated['platform']) . ' account updated successfully',
                    'data' => $existingAccount
                ]);
            }

            // Create new social account
            $socialAccount = $profile->socialAccounts()->create([
                'platform' => $validated['platform'],
                'account_name' => $accountName,
                'access_token' => $accessToken
            ]);

            return response()->json([
                'status' => 'success',
                'message' => ucfirst($validated['platform']) . ' account connected successfully',
                'data' => $socialAccount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to connect page:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect page: ' . $e->getMessage()
            ], 500);
        }
    }
} 