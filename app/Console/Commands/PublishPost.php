<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-postii';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'publish a post to social media account in a scheduled time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $posts = Post::where('status', 'queued')
            ->where('scheduled_time', '<=', $now)
            ->get();
        if ($posts->isEmpty()) {
            $this->info('No posts to publish at this time.');
            return;
        }
        foreach ($posts as $post) {
            // api call
            $post->status = 'sent';
            $post->published_at = $now;
            $post->save();

            $this->info("Post ID {$post->id} published successfully.");
        }
    }
}
