<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'name' => 'required|string|max:50|unique:tags'
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

    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id
        ]);

        $tag->update($request->only('name'));

        return response()->json([
            'status' => 'success',
            'message' => 'Tag updated successfully',
            'data' => $tag
        ]);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Tag deleted successfully'
        ]);
    }
}