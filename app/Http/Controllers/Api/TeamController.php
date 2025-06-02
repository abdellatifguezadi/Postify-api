<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $teams = $user->teams()->with(['users', 'profiles'])->get();
        
        return response()->json($teams);
    }

    /**
     * Join a team using its slug
     */
    // public function joinTeam(Request $request)
    // {


    //     return response()->json($team->load('users'));
    // }

    // /**
    //  * Leave a team
    //  */
    // public function leaveTeam(Team $team)
    // {


    //     return response()->json(['message' => 'Successfully left the team']);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {


    //     return response()->json($team->load(['users', 'profiles']), 201);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Team $team)
    // {
    //     // Verify if the authenticated user belongs to this team
    //     if (!$team->users()->where('user_id', auth()->id())->exists()) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     return response()->json($team->load(['users', 'profiles']));
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Team $team)
    // {


    //     return response()->json($team->load(['users', 'profiles']));
    // }


 
}


