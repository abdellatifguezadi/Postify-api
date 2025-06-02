<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $invites = Invite::where('receiver_id', $user->id)
            ->with(['team', 'sender'])
            ->get();

        return response()->json($invites);
    }


    public function inviteUser(Request $request,  $teamId)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $team = Team::findOrFail($teamId);

        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'You are not a member of this team'], 403);
        }

        $receiver = User::where('email', $validatedData['email'])->first();

        $alreadyMember = $team->users()->where('user_id', $receiver->id)->exists();
        if ($alreadyMember) {
            return response()->json(['message' => 'User is already a member of the team'], 400);
        }

        $existingInvite = Invite::where('team_id', $teamId)
            ->where('receiver_id', $receiver->id)
            ->exists();

        if ($existingInvite) {
            return response()->json(['message' => 'User has already been invited to this team'], 400);
        }

        $invite = Invite::create([
            'team_id' => $teamId,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver->id,
        ]);

        return response()->json(['message' => 'Invite sent!', 'invite' => $invite]);
    }

    public function acceptInvite(Request $request, $inviteId)
    {
        $invite = Invite::findOrFail($inviteId);

        if ($invite->receiver_id !== Auth::id()) {
            return response()->json(['message' => 'You are not authorized to accept this invite'], 403);
        }

        $team = $invite->team;

        if (!$team->users()->where('user_id', Auth::id())->exists()) {
            $team->users()->attach(Auth::id());
            $invite->delete(); 
            return response()->json(['message' => 'Invite accepted successfully']);
        }

        return response()->json(['message' => 'You are already a member of this team'], 400);
    }

    public function rejectInvite($inviteId)
    {
        $invite = Invite::findOrFail($inviteId);

        if( $invite->receiver_id !== Auth::id()){
            return response()->json(['message' => 'You are not authorized to reject this invite'], 403);
        }

        $invite->delete();
        return response()->json(['message' => 'Invite rejected successfully']);
    }


    

}
