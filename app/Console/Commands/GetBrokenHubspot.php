<?php

namespace App\Console\Commands;

use App\Models\Hubspot\Broken;
use App\Models\Hubspot\Site;
use App\Rules\SiteCheckTest;
use HubSpot\Factory;
use Illuminate\Console\Command;

class GetBrokenHubspot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:get-broken {form}';

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
            'path' => '/form-integrations/v1/submissions/forms/'.$this->argument('form'),
            'qs' => [
                'limit' => 50,
                'after' => null,
            ]
        ]);

        $response = json_decode($response->getBody()->getContents());

        foreach ($response->results as $result)
        {
            $form = $result->values;

            if (Broken::query()
                ->where('submitted_at', $result->submittedAt)
                ->where('form', $this->argument('form'))
                ->first())

                continue;

            $site = new Broken();
            $site->body = json_encode($form);
            $site->submitted_at = $result->submittedAt;
            $site->form = $this->argument('form');
            $site->status = 0;
            $site->is_double = false;

            foreach ($form as $item) {

                try {
                    if ($item->name == 'phone_number') {

                        $site->phone = $item->value;
                        $site->save();

                    } elseif ($item->name == 'course_name') {

                        $site->coursename = $item->value;
                        $site->save();
                    } else {

                        $site->{$item->name} = $item->value;
                        $site->save();
                    }

                } catch (\Throwable $e) {

                    dump(__METHOD__, [$e->getMessage()]);
                }
            }
            $site->is_test = SiteCheckTest::isTest($site);
            $site->save();

            if ($site->coursetype != 'course' && $site->coursetype != 'yearly-program') {

                $site->status = 4;
                $site->save();
            }
        }

        return Command::SUCCESS;
    }
}
