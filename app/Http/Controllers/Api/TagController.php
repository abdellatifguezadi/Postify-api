<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::where('user_id', Auth::id())
            ->withCount('posts')
            ->get();
        
        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,NULL,id,user_id,' . Auth::id(),
        ]);

        $tag = new Tag([
            'name' => $request->name,
            'user_id' => Auth::id()
        ]);
        
        $tag->save();

        return response()->json($tag, 201);
    }

    public function show(Tag $tag)
    {
        if (Auth::id() !== $tag->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($tag->load('posts'));
    }

    public function update(Request $request, Tag $tag)
    {
        if (Auth::id() !== $tag->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id . ',id,user_id,' . Auth::id(),
        ]);

        $tag->name = $request->name;
        $tag->save();

        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        if (Auth::id() !== $tag->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $tag->delete();
        
        return response()->json(['message' => 'Tag deleted successfully']);
    }
} 