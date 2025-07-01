<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xxx {socialAccount?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(SocialAccount $socialAccount)
    {
        $post = new Post([
            'content' => 'This is a test post',
            'status' => 'draft',
            'scheduled_time' => now()->addHour(),
        ]);
        $post->social_account_id = $socialAccount->id;
        $post->save();

        $this->info('Post created successfully!');
    }
}
