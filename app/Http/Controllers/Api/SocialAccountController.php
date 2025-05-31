<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Profile;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    public function index(Profile $profile)
    {
        $accounts = $profile->socialAccounts;
        
        return response()->json([
            'status' => 'success',
            'data' => $accounts
        ]);
    }

    public function store(Profile $profile, Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:50',
            'account_name' => 'required|string',
            'access_token' => 'required|string',
            'account_details' => 'nullable|json'
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
            'data' => $socialAccount
        ]);
    }

    public function update(Profile $profile, $socialAccountId, Request $request)
    {
        $socialAccount = $profile->socialAccounts()->findOrFail($socialAccountId);

        $validated = $request->validate([
            'platform' => 'sometimes|required|string|max:50',
            'account_name' => 'sometimes|required|string',
            'access_token' => 'sometimes|required|string',
            'account_details' => 'nullable|json'
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