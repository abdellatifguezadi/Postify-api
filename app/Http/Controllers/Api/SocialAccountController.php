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

class SocialAccountController extends Controller
{
    public function index(Profile $profile)
    {
        $accounts = $profile->socialAccounts;
        
        // Include access_token for testing purposes
        $accountsWithTokens = $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'profile_id' => $account->profile_id,
                'platform' => $account->platform,
                'account_name' => $account->account_name,
                'access_token' => $account->access_token, // Include token for testing
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $accountsWithTokens
        ]);
    }

    public function store(Profile $profile, Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string',
            'account_name' => 'required|string',
            'access_token' => 'required|string'
        ]);
        
        $socialAccount = $profile->socialAccounts()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Social account created successfully',
            'data' => $socialAccount
        ], 201);
    }

    public function show(Profile $profile, $socialAccountId)
    {
        $socialAccount = $profile->socialAccounts()->findOrFail($socialAccountId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $socialAccount->id,
                'profile_id' => $socialAccount->profile_id,
                'platform' => $socialAccount->platform,
                'account_name' => $socialAccount->account_name,
                'access_token' => $socialAccount->access_token,
                'created_at' => $socialAccount->created_at,
                'updated_at' => $socialAccount->updated_at
            ]
        ]);
    }

    public function update(Profile $profile, $socialAccountId, Request $request)
    {
        $socialAccount = $profile->socialAccounts()->findOrFail($socialAccountId);

        $validated = $request->validate([
            'platform' => 'sometimes|required|string',
            'account_name' => 'sometimes|required|string',
            'access_token' => 'sometimes|required|string'
        ]);

        $socialAccount->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Social account updated successfully',
            'data' => $socialAccount
        ]);
    }

    public function destroy(Profile $profile, $socialAccountId)
    {
        $socialAccount = $profile->socialAccounts()->findOrFail($socialAccountId);
        
        $socialAccount->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Social account deleted successfully'
        ]);
    }


    /**
     * Get Facebook login URL with state parameter (more secure)
     */
    public function getFacebookLoginUrlWithState(Profile $profile)
    {
        $state = base64_encode(json_encode(['profile_id' => $profile->id]));
        
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
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            $code = $request->input('code');
            if (!$code) {
                throw new \Exception('Authorization code is missing');
            }

            // Try to get profile ID from state parameter first
            $state = $request->input('state');
            $profileId = null;

            if ($state) {
                try {
                    $stateData = json_decode(base64_decode($state), true);
                    if (isset($stateData['profile_id'])) {
                        $profileId = $stateData['profile_id'];
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
            $facebookUser = Socialite::driver('facebook')
                ->redirectUrl(config('services.facebook.redirect'))
                ->stateless()
                ->user();

            // Check if social account already exists
            $existingAccount = $profile->socialAccounts()
                ->where('platform', 'facebook')
                ->where('account_name', $facebookUser->getName())
                ->first();

            if ($existingAccount) {
                // Update existing account
                $existingAccount->update([
                    'access_token' => $facebookUser->token
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facebook account updated successfully',
                    'data' => $existingAccount
                ]);
            }

            // Create new social account
            $socialAccount = $profile->socialAccounts()->create([
                'platform' => 'facebook',
                'account_name' => $facebookUser->getName(),
                'access_token' => $facebookUser->token
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Facebook account connected successfully',
                'data' => $socialAccount
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook callback error:', [
                'error' => $e->getMessage(),
                'state' => $request->input('state'),
                'code' => $request->input('code')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect Facebook account: ' . $e->getMessage(),
                'debug' => [
                    'state' => $request->input('state'),
                    'code' => $request->input('code')
                ]
            ], 500);
        }
    }

 
} 