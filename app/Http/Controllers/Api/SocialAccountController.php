<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Profile;
use Illuminate\Http\Request;
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
} 