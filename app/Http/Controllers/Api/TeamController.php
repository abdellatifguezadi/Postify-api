<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $teams = $user->teams()->with('users', 'profiles')->get();
        
        return response()->json([
            'user' => $user,
            'teams' => $teams
        ]);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $team = new Team();
        $team->name = $validatedData['name'];
        $team->slug = Str::slug($validatedData['name'] . '-' . now()->format('dmYHis'));
        if ($request->hasFile('logo')) {
            $team->logo = $request->file('logo')->store('logos', 'public');
        }
        $team->save();
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        $team->users()->attach($user->id);

        return response()->json($team->load(['users']), 201);
    }


    public function show(Team $team)
    {
        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($team->load(['users', 'profiles']));
    }


    public function update(Request $request, Team $team)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $team->name = $validatedData['name'];
        $team->slug = Str::slug($validatedData['name'] . '-' . now()->format('dmYHis'));
        if ($request->hasFile('logo')) {
            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }
            $team->logo = $request->file('logo')->store('logos', 'public');
        }
        $team->save();
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        if (!$team->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $team->users()->syncWithoutDetaching($user->id);
        return response()->json($team->load(['users']));
    }






}


