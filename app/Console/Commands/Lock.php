<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LockWindowsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'windows:lock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lock the Windows workstation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Execute the Windows command to lock the workstation
        exec('rundll32.exe user32.dll,LockWorkStation');

        $this->info('Windows workstation locked successfully.');
    }
}
