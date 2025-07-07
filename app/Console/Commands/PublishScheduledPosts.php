<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{

    protected $signature = 'posts:publish';
    protected $description = 'Publish scheduled posts';

    public function handle()
    {
        $posts = Post::scheduled()->get();

        foreach ($posts as $post) {
            $post->update(['is_published' => true]);
            // tiktook api
        }

        $this->info('Published ' . $posts->count() . ' posts.');
    }
}
