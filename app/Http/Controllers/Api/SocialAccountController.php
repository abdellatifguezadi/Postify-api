<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialAccountController extends Controller
{
    public function index()
    {
        $accounts = SocialAccount::where('user_id', Auth::id())->get();
        return response()->json([
            'social_accounts' => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:facebook,instagram,tiktok',
            'account_name' => 'required|string',
            'access_token' => 'required|string',
            'account_details' => 'nullable|json'
        ]);

        $validated['user_id'] = Auth::id();
        $socialAccount = SocialAccount::create($validated);

        return response()->json([
            'message' => 'Social account created successfully',
            'social_account' => $socialAccount
        ], 201);
    }

    public function show(SocialAccount $socialAccount)
    {
        if (Auth::id() !== $socialAccount->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'social_account' => $socialAccount
        ]);
    }

    public function update(Request $request, SocialAccount $socialAccount)
    {
        if (Auth::id() !== $socialAccount->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'account_name' => 'sometimes|string',
            'access_token' => 'sometimes|string',
            'account_details' => 'nullable|json'
        ]);

        $socialAccount->update($validated);

        return response()->json([
            'message' => 'Social account updated successfully',
            'social_account' => $socialAccount
        ]);
    }

    public function destroy(SocialAccount $socialAccount)
    {
        if (Auth::id() !== $socialAccount->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $socialAccount->delete();
        
        return response()->json([
            'message' => 'Social account deleted successfully'
        ]);
    }
} 