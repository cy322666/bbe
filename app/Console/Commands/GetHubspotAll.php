<?php

namespace App\Console\Commands;

use App\Models\Hubspot\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GetHubspotAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:get-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): int
    {
        $formIds = config('services.hubspot');

        foreach ($formIds as $formId) {

            Artisan::call('hubspot:get', ['form' => $formId]);
        }
    }
}
