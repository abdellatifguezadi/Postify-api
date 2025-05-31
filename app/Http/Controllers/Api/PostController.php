<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Media;
use App\Models\Tag;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(SocialAccount $socialAccount)
    {
        $posts = $socialAccount->posts()
            ->with(['medias', 'socialAccount', 'tags'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required_without:media|string|max:2200',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:102400',
            'social_account_ids' => 'required|array',
            'social_account_ids.*' => 'exists:social_accounts,id',
            'scheduled_time' => 'nullable|date',
            'status' => 'required|in:draft,queued',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $posts = [];
        $mediaFiles = [];


        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('posts/media', 'public');
                $type = explode('/', $file->getMimeType())[0];
                
                $mediaFiles[] = [
                    'path' => $path,
                    'type' => $type
                ];
            }
        }


        foreach ($request->social_account_ids as $accountId) {
            $post = new Post([
                'content' => $request->content,
                'social_account_id' => $accountId,
                'status' => $request->status,
                'scheduled_time' => $request->scheduled_time
            ]);
            
            $post->save();


            if ($request->has('tags')) {
                $post->tags()->attach($request->tags);
            }


            foreach ($mediaFiles as $mediaFile) {
                $newPath = 'posts/media/' . uniqid() . '_' . basename($mediaFile['path']);
                Storage::disk('public')->copy($mediaFile['path'], $newPath);
                
                $media = new Media([
                    'path' => $newPath,
                    'type' => $mediaFile['type']
                ]);
                
                $post->medias()->save($media);
            }

            $posts[] = $post->load(['medias', 'socialAccount', 'tags']);
        }


        foreach ($mediaFiles as $mediaFile) {
            Storage::disk('public')->delete($mediaFile['path']);
        }

        return response()->json([
            'status' => 'success',
            'message' => count($posts) . ' posts created successfully',
            'data' => $posts
        ], 201);
    }

    public function show(SocialAccount $socialAccount, Post $post)
    {
        if ($post->social_account_id !== $socialAccount->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post does not belong to this social account'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $post->load(['medias', 'socialAccount', 'tags'])
        ]);
    }

    public function update(Request $request, SocialAccount $socialAccount, Post $post)
    {
        if ($post->social_account_id !== $socialAccount->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post does not belong to this social account'
            ], 403);
        }

        $request->validate([
            'content' => 'required_without:media|string|max:2200',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:102400',
            'scheduled_time' => 'nullable|date',
            'status' => 'required|in:draft,queued',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $post->update([
            'content' => $request->content,
            'status' => $request->status,
            'scheduled_time' => $request->scheduled_time
        ]);

        $post->tags()->sync($request->tags ?? []);

        if ($request->hasFile('media')) {

            foreach ($post->medias as $media) {
                Storage::disk('public')->delete($media->path);
                $media->delete();
            }


            foreach ($request->file('media') as $file) {
                $path = $file->store('posts/media', 'public');
                $type = explode('/', $file->getMimeType())[0];
                
                $media = new Media([
                    'path' => $path,
                    'type' => $type
                ]);
                
                $post->medias()->save($media);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Post updated successfully',
            'data' => $post->load(['medias', 'socialAccount', 'tags'])
        ]);
    }

    public function destroy(SocialAccount $socialAccount, Post $post)
    {
        if ($post->social_account_id !== $socialAccount->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post does not belong to this social account'
            ], 403);
        }

        foreach ($post->medias as $media) {
            Storage::disk('public')->delete($media->path);
        }
        
        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }

    public function changeStatus(Request $request, SocialAccount $socialAccount, Post $post)
    {
        if ($post->social_account_id !== $socialAccount->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post does not belong to this social account'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:draft,queued,sent'
        ]);

        $newStatus = $request->status;
        $currentStatus = $post->status;

        if ($newStatus === 'queued' && $currentStatus !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft posts can be moved to queued'
            ], 400);
        }

        if ($newStatus === 'draft' && $currentStatus !== 'queued') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only queued posts can be moved to draft'
            ], 400);
        }

        if ($newStatus === 'sent' && !in_array($currentStatus, ['draft', 'queued'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft or queued posts can be moved to sent'
            ], 400);
        }

        $post->status = $newStatus;
        
        if ($newStatus === 'sent') {
            $post->published_at = now();
        }
        
        $post->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post status updated successfully',
            'data' => $post->load(['medias', 'socialAccount', 'tags'])
        ]);
    }

    public function updateSchedule(Request $request, SocialAccount $socialAccount, Post $post)
    {
        if ($post->social_account_id !== $socialAccount->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post does not belong to this social account'
            ], 403);
        }

        $request->validate([
            'scheduled_time' => 'required|date'
        ]);

        $post->scheduled_time = $request->scheduled_time;
        $post->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Post schedule updated successfully',
            'data' => $post->load(['medias', 'socialAccount', 'tags'])
        ]);
    }

    public function duplicate(SocialAccount $socialAccount, Post $post)
    {
        if ($post->social_account_id !== $socialAccount->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post does not belong to this social account'
            ], 403);
        }

        $newPost = $post->replicate();
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->save();

        $newPost->tags()->attach($post->tags->pluck('id'));

        foreach ($post->medias as $media) {
            $newPath = 'posts/media/' . uniqid() . '_' . basename($media->path);
            Storage::disk('public')->copy($media->path, $newPath);
            
            $newMedia = new Media([
                'path' => $newPath,
                'type' => $media->type
            ]);
            
            $newPost->medias()->save($newMedia);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Post duplicated successfully',
            'data' => $newPost->load(['medias', 'socialAccount', 'tags'])
        ]);
    }
} 