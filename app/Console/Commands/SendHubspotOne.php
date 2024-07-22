<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Hubspot\Site;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SendHubspotOne extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:send-one {site}';

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
        $this->amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initCache();

        $site = Site::query()->find($this->argument('site'));

        $site->is_double = Site::query()
            ->where('id', '!=', $site->id)
            ->where('email', $site->email)
            ->whereDate('created_at', '>', Carbon::now()->subDay()->format('Y-m-d'))
            ->exists();

        $site->save();

//        Artisan::call('hubspot:send', ['site' => $site->id]);

        return Command::SUCCESS;
    }
}
