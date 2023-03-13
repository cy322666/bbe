<?php

namespace App\Console\Commands;

use App\Jobs\Segment;
use App\Models\Account;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bbe:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private Client $amoApi;

    public function handle()
    {
        $this->amoApi = (new Client(Account::first()))->init();

        Segment::dispatch(\App\Models\Segment::first());

        return CommandAlias::SUCCESS;
    }
}
