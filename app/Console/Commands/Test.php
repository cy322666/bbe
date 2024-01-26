<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Services\Site\SiteAction;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

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
        $action = new SiteAction((new Client(Account::query()->first()))->init());

        $site = Site::find(10644);

        $action->send($site, json_decode($site->body));

        return Command::SUCCESS;
    }
}
