<?php

namespace App\Console\Commands;

use App\Models\Hubspot\Segment;
use App\Models\Hubspot\Site;
use App\Rules\SiteCheckTest;
use App\Services\amoCRM\Models\Contacts;
use HubSpot\Factory;
use Illuminate\Console\Command;

class GetHubspotSegment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:get-segment-python';

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
        $hubspot = Factory::createWithAccessToken(env('HUBSPOT_TOKEN'));

        $response = $hubspot->apiRequest([
            'path' => '/contacts/v1/lists/2370/contacts/all',
            'qs' => [
                'limit' => 20,
                'after' => null,
            ]
        ]);

        $response = json_decode($response->getBody()->getContents());

        foreach ($response->contacts as $result) {

            $fields = (array)$result;

            $vid = $fields['identity-profiles'][0]->vid;

            $response = $hubspot->apiRequest([
                'path' => '/contacts/v1/contact/vid/'.$vid.'/profile',
            ]);

            $response = json_decode($response->getBody()->getContents());

            if (!Segment::query()->where('vid', $vid)->exists()) {

                $seg = Segment::query()->create([
                    'vid'   => $vid,
                    'email' => $response->properties->email->value ?? null,
                    'phone' => Contacts::clearPhone($response->properties->phone->value ?? null),
                    'firstname' => $response->properties->firstname->value ?? null,
                    'body' => json_encode($response->properties),
                ]);

                $seg->is_test = SiteCheckTest::isTest($seg);
                $seg->save();

            }
        }
    }
}
