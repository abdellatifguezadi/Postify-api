<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Team;
use Illuminate\Http\Request;

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
            'avatar' => 'nullable|image|max:2048',
        ]);

        $profile = $team->profiles()->create($request->all());

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
            'avatar' => 'nullable|image|max:2048',
        ]);

        $profile->update($request->all());

        return response()->json($profile->load(['socialAccounts', 'columns']));
    }

    public function destroy(Profile $profile)
    {
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
