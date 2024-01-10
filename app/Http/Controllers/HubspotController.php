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

    public function getBroken()
    {
        Artisan::call('hubspot:get-broken', ['form' => 'c8970cb9-5052-437e-939b-94f388a92312']);
    }

    public function pushBroken()
    {
        Artisan::call('hubspot:send-broken');
    }

    public function getSegmentPython()
    {
        Artisan::call('hubspot:get-segment-python');
    }

    public function sendSegment()
    {

    }
}
