<?php

namespace App\Http\Controllers;

use App\Models\Hubspot\Site;
use Illuminate\Support\Facades\Artisan;

class HubspotController extends Controller
{
    public function push()
    {
        $sites = Site::query()
            ->where('status', 0)
            ->get();

        foreach ($sites as $site) {

            dd($site);
            //command
        }
    }

    public function cron()
    {
        $formIds = config('services.hubspot');

        foreach ($formIds as $formId) {

            Artisan::call('hubspot:get', ['form' => $formId]);
        }
    }
}
