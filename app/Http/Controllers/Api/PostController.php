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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // Check and update scheduled posts before returning the list
        $this->checkAndUpdateScheduledPosts();

        $query = Post::with(['medias', 'socialAccounts', 'tags'])
            ->where('user_id', Auth::id());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->social_account_id) {
            $query->whereHas('socialAccounts', function($q) use ($request) {
                $q->where('social_accounts.id', $request->social_account_id);
            });
        }

        if ($request->tag) {
            $query->withTag($request->tag);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required_without:media|string|max:2200',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:102400',
            'social_accounts' => 'required|array',
            'social_accounts.*' => 'exists:social_accounts,id',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:draft,queue',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $post = new Post();
        $post->content = $request->content;
        $post->user_id = Auth::id();
        $post->status = $request->status;
        $post->scheduled_date = $request->scheduled_date;
        $post->scheduled_time = $request->scheduled_time;
        $post->save();

        // Attach social accounts
        $post->socialAccounts()->attach($request->social_accounts);

        // Attach tags
        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('posts/media', 'public');
                
                // Get the main type from the MIME type (e.g., 'image' from 'image/jpeg')
                $type = explode('/', $file->getMimeType())[0];
                
                $media = new Media([
                    'path' => $path,
                    'type' => $type
                ]);
                
                $post->medias()->save($media);
            }
        }

        return response()->json($post->load(['medias', 'socialAccounts', 'tags']));
    }

    public function update(Request $request, Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required_without:media|string|max:2200',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:102400',
            'social_accounts' => 'required|array',
            'social_accounts.*' => 'exists:social_accounts,id',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:draft,queue',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $post->content = $request->content;
        $post->scheduled_date = $request->scheduled_date;
        $post->scheduled_time = $request->scheduled_time;
        $post->status = $request->status;
        $post->save();

        // Update social accounts
        $post->socialAccounts()->sync($request->social_accounts);

        // Update tags
        $post->tags()->sync($request->tags ?? []);

        // Handle media updates
        if ($request->hasFile('media')) {
            // Delete old media
            foreach ($post->medias as $media) {
                Storage::disk('public')->delete($media->path);
                $media->delete();
            }

            // Add new media
            foreach ($request->file('media') as $file) {
                $path = $file->store('posts/media', 'public');
                
                // Get the main type from the MIME type (e.g., 'image' from 'image/jpeg')
                $type = explode('/', $file->getMimeType())[0];
                
                $media = new Media([
                    'path' => $path,
                    'type' => $type
                ]);
                
                $post->medias()->save($media);
            }
        }

        return response()->json($post->load(['medias', 'socialAccounts', 'tags']));
    }

    public function show(Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($post->load(['medias', 'socialAccounts', 'tags']));
    }

    public function destroy(Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete associated media files
        foreach ($post->medias as $media) {
            Storage::disk('public')->delete($media->path);
        }
        
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function changeStatus(Request $request, Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:draft,queue,sent'
        ]);

        $newStatus = $request->status;
        $currentStatus = $post->status;

        // Check valid transitions
        if ($newStatus === 'queue' && $currentStatus !== 'draft') {
            return response()->json(['message' => 'Only draft posts can be moved to queue'], 400);
        }

        if ($newStatus === 'draft' && $currentStatus !== 'queue') {
            return response()->json(['message' => 'Only queued posts can be moved to draft'], 400);
        }

        if ($newStatus === 'sent' && !in_array($currentStatus, ['draft', 'queue'])) {
            return response()->json(['message' => 'Only draft or queued posts can be moved to sent'], 400);
        }

        $post->status = $newStatus;
        
        if ($newStatus === 'sent') {
            $post->published_at = now();
        }
        
        $post->save();

        return response()->json($post->load(['medias', 'socialAccounts', 'tags']));
    }

    public function updateSchedule(Request $request, Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|date_format:H:i'
        ]);

        $post->scheduled_date = $request->scheduled_date;
        $post->scheduled_time = $request->scheduled_time;
        $post->save();

        return response()->json($post->load(['medias', 'socialAccounts', 'tags']));
    }

    public function duplicate(Post $post)
    {
        // Pour la duplication, on vérifie juste si l'utilisateur est authentifié
        // car il peut dupliquer n'importe quel post auquel il a accès
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $newPost = $post->replicate();
        $newPost->user_id = Auth::id(); // Assigner le nouveau post à l'utilisateur actuel
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->save();

        // Duplicate social accounts
        $newPost->socialAccounts()->attach($post->socialAccounts->pluck('id'));

        // Duplicate tags
        $newPost->tags()->attach($post->tags->pluck('id'));

        // Duplicate media
        foreach ($post->medias as $media) {
            $newPath = 'posts/media/' . uniqid() . '_' . basename($media->path);
            Storage::disk('public')->copy($media->path, $newPath);
            
            $newMedia = new Media([
                'path' => $newPath,
                'type' => $media->type
            ]);
            
            $newPost->medias()->save($newMedia);
        }

        return response()->json($newPost->load(['medias', 'socialAccounts', 'tags']));
    }

    private function checkAndUpdateScheduledPosts()
    {
        $now = now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i');

        // Update all queued posts that have reached their scheduled time
        Post::where('status', 'queue')
            ->where(function($query) use ($currentDate, $currentTime) {
                $query->where('scheduled_date', '<', $currentDate)
                    ->orWhere(function($q) use ($currentDate, $currentTime) {
                        $q->where('scheduled_date', $currentDate)
                           ->where('scheduled_time', '<=', $currentTime);
                    });
            })
            ->update([
                'status' => 'sent',
                'published_at' => now()
            ]);
    }
} 