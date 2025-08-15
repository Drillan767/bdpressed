<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdp:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs post deploy commands';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Running post deploy commands');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('queue:clear', ['--force' => true]);
        $this->call('queue:restart');
        $this->call('migrate', ['--force' => true, '--seed' => true]);
        $this->call('storage:link');
        $this->info('Done.');
    }
}
