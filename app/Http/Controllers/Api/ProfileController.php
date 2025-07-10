<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(Team $team)
    {
        $profiles = $team->profiles()
            ->with(['socialAccounts', 'columns'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($profiles);
    }

    public function show(Profile $profile)
    {
        $profile->load(['socialAccounts', 'columns']);
        return response()->json($profile);
    }

    public function store(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $profileData = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        // Gestion de l'upload d'avatar
        if ($request->hasFile('avatar')) {
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile = $team->profiles()->create($profileData);

        // Création des colonnes par défaut
        $profile->columns()->create([
            'name' => 'To Do',
        ]);
        $profile->columns()->create([
            'name' => 'In Progress',
        ]);
        $profile->columns()->create([
            'name' => 'Done',
        ]);

        return response()->json($profile->load('columns'), 201);
    }

    public function update(Request $request, Profile $profile)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $profileData = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        // Gestion de l'upload d'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar s'il existe
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($profileData);

        return response()->json($profile->load(['socialAccounts', 'columns']));
    }

    public function destroy(Profile $profile)
    {
        // Supprimer l'avatar s'il existe
        if ($profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $profile->delete();
        return response()->json(null, 204);
    }

    public function getSocialAccounts(Profile $profile)
    {
        $socialAccounts = $profile->socialAccounts()
            ->with('posts')
            ->get();
        return response()->json($socialAccounts);
    }

    public function getColumns(Profile $profile)
    {
        $columns = $profile->columns()
            ->with('tasks')
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json($columns);
    }
}
