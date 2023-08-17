<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;

class SiteSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:send {site_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $site = Site::query()->find($this->argument('site_id'));

        \App\Services\amoCRM\Services\Site\SiteSend::send($site);

        return Command::SUCCESS;
    }
}
