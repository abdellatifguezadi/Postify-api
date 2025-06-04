<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{

    public function index()
    {
        $tags = Tag::all();
        return response()->json([
            'status' => 'success',
            'data' => $tags
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:50|unique:tags"
        ]);

        $tag = Tag::create($request->only('name'));

        return response()->json([
            'status' => 'success',
            'message' => 'Tag created successfully',
            'data' => $tag
        ], 201);
    }

    public function show(Tag $tag)
    {
        return response()->json([
            'status' => 'success',
            'data' => $tag
        ]);
    }


    public function destroy(Tag $tag)
    {
        if ($tag->posts()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this tag because it is associated with one or more posts.'
            ], 400);
        }

        $tag->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tag deleted successfully'
        ]);
    }

    public function tagByPost(Post $post)
    {
        $tags = $post->tags()->get();
        return response()->json([
            'status' => 'success',
            'data' => $tags
        ]);
    }
}
