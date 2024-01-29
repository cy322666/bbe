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
        $client = (new Client(Account::query()->first()))->init();

        $lead = $client->service->leads()->find(29055861);

        $lead->cf('lead_id')->setValue('test');
        $lead->cf('registration')->setValue(1111);
        $lead->save();

        return Command::SUCCESS;
    }
}
