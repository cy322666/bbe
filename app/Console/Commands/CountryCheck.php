<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Country;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use Illuminate\Console\Command;

class CountryCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'country:check {lead_id}';

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
        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        $lead = $amoApi->service->leads()->find($this->argument('lead_id'));

        $contact = $lead->contact;

        $country = false;

        if ($contact->cf('Страна')->getValue()) {

            $phone = Contacts::clearPhone($contact->cf('Телефон')->getValue());

            //проверка на рф и каз
            $prefixPhone = substr($phone, 0, 1);

            if ($prefixPhone == 7 || $prefixPhone == 8) {

                $prefixPhone = substr($phone, 0, 2);

                if ($prefixPhone == 77)
                    $country = Country::query()->where('country', 'Казахстан')->first();
                else
                    $country = Country::query()->where('country', 'Российская Федерация')->first();
            }

            if (!$country) {

                $prefixPhone = substr($phone, 0, 3);

                $country = Country::query()->where('key', $prefixPhone)->first();

                if (!$country) {

                    $prefixPhone = substr($phone, 0, 2);

                    $country = Country::query()->where('key', $prefixPhone)->first();

                    if (!$country) {

                        $prefixPhone = substr($phone, 0, 1);

                        $country = Country::query()->where('key', $prefixPhone)->first();
                    }
                }
            }

            if ($country) {

                $contact->cf('Страна')->setValue($country->country);
                $contact->save();
            }
        }

        return Command::SUCCESS;
    }
}
