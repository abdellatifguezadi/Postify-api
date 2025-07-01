<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Carbon\Carbon;

class ProcessScheduledPosts extends Command
{
    protected $signature = 'posts:process-scheduled';
    protected $description = 'Process scheduled posts and change their status from queued to sent';

    public function handle()
    {
        $now = Carbon::now();
        $this->info("Current time: " . $now);

        $posts = Post::where('status', 'queued')
            ->where('scheduled_time', '<=', $now)
            ->get();

        $this->info("Found " . $posts->count() . " posts to process");

        if ($posts->isEmpty()) {
            $allPosts = Post::where('status', 'queued')->get();
            $this->info("Total queued posts: " . $allPosts->count());
            
            foreach ($allPosts as $post) {
                $this->info("Post ID: {$post->id}, Scheduled Time: {$post->scheduled_time}, Current Time: {$now}");
            }
        }

        foreach ($posts as $post) {
            $post->update([
                'status' => 'sent',
                'published_at' => $now
            ]);

            $this->info("Post ID {$post->id} has been marked as sent.");
        }

        $this->info('Scheduled posts processing completed.');
    }
} 