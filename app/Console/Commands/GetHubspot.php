<?php

namespace App\Console\Commands;

use App\Models\Hubspot\Site;
use App\Rules\SiteCheckTest;
use Carbon\Carbon;
use HubSpot\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetHubspot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:get ?{form} ?{type}'; //add form id

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $hubspot = Factory::createWithAccessToken(env('HUBSPOT_TOKEN'));

        $response = $hubspot->apiRequest([
            'path' => '/form-integrations/v1/submissions/forms/'.$this->argument('form'),
            'qs' => [
                'limit' => 20,
                'after' => null,
            ]
        ]);

        $response = json_decode($response->getBody()->getContents());

        foreach ($response->results as $result)
        {
            $form = $result->values;

            if (Site::query()
                ->where('submitted_at', $result->submittedAt)
                ->where('form', $this->argument('form'))
                ->exists()) {

                continue;
            }

            $site = new Site();
            $site->body = json_encode($form);
            $site->submitted_at = $result->submittedAt;
            $site->form = $this->argument('form');
            $site->type = $this->argument('type');
            $site->is_test = SiteCheckTest::isTest($site);
            $site->save();

            $site->is_double = Site::query()
                ->where('id', '!=', $site->id)
                ->where('form', $this->argument('form'))
                ->where('email', $site->email)
                ->exists();

            $site->save();

            foreach ($form as $item) {

                try {
                    if ($item->name == 'phone_number') {

                        $site->phone = $item->value;
                        $site->save();

                    } elseif ($item->name == 'course_name') {

                        $site->coursename = $item->value;
                        $site->save();
                    } else {

                        try {
                            $site->{$item->name} = $item->value;

                        } catch (\Throwable) {}

                        $site->save();
                    }

                } catch (\Throwable $e) {

                    Log::alert(__METHOD__, [$e->getMessage()]);
                }
            }
        }
    }
}
