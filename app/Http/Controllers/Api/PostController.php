<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Media;
use App\Models\Tag;
use App\Models\SocialAccount;
use App\Helpers\MediaHelper;
use Illuminate\Http\Request;
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

    public function getPostsByStatus(SocialAccount $socialAccount, $status)
    {
        if (!in_array($status, ['draft', 'queued', 'sent'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid status. Status must be draft, queued or sent'
            ], 400);
        }

        $posts = $socialAccount->posts()
            ->where('status', $status)
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
            'status' => 'required|in:draft,queued',
            'scheduled_time' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $posts = [];
        $mediaFiles = $request->hasFile('media') ? $request->file('media') : [];

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

            if (!empty($mediaFiles)) {
                $storedMedia = MediaHelper::storeMedia($mediaFiles, $post->id);
                foreach ($storedMedia as $mediaFile) {
                    $media = new Media([
                        'path' => $mediaFile['path'],
                        'type' => $mediaFile['type']
                    ]);
                    
                    $post->medias()->save($media);
                }
            }

            $posts[] = $post->load(['medias', 'socialAccount', 'tags']);
        }

        return response()->json([
            'status' => 'success',
            'message' => count($posts) . ' posts created successfully',
            'data' => $posts
        ], 201);
    }

    public function storeAccount(Request $request, SocialAccount $socialAccount)
    {
        $request->validate([
            'content' => 'required_without:media|string|max:2200',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:102400',
            'scheduled_time' => 'nullable|date',
            'status' => 'required|in:draft,queued',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $post = new Post([
            'content' => $request->content,
            'social_account_id' => $socialAccount->id,
            'status' => $request->status,
            'scheduled_time' => $request->scheduled_time
        ]);
        
        $post->save();

        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

        if ($request->hasFile('media')) {
            $mediaFiles = MediaHelper::storeMedia($request->file('media'), $post->id);
            foreach ($mediaFiles as $mediaFile) {
                $media = new Media([
                    'path' => $mediaFile['path'],
                    'type' => $mediaFile['type']
                ]);
                
                $post->medias()->save($media);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully',
            'data' => $post->load(['medias', 'socialAccount', 'tags'])
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
            $oldMediaPaths = $post->medias->pluck('path')->toArray();
            MediaHelper::deleteMediaFiles($oldMediaPaths);
            $post->medias()->delete();
            $mediaFiles = MediaHelper::storeMedia($request->file('media'), $post->id);

            foreach ($mediaFiles as $mediaFile) {
                $media = new Media([
                    'path' => $mediaFile['path'],
                    'type' => $mediaFile['type']
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

        $mediaPaths = $post->medias->pluck('path')->toArray();
        MediaHelper::deleteMediaFiles($mediaPaths);
        
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
            $newMediaData = MediaHelper::duplicateMedia($media);
            
            $newMedia = new Media([
                'path' => $newMediaData['path'],
                'type' => $newMediaData['type']
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