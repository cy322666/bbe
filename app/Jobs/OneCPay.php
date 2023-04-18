<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class OneCPay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Pay $pay) {}

    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $amoApi->service->queries->setDelay(1);

        $contact = Contacts::search(['Почта' => $this->pay->email], $amoApi);

        $leads = $contact
            ->leads
            ->filter(function($lead) {

                return $lead->status_id == 142 && $lead->pipeline_id == 3342043;
        });

        $lead = $leads->count() > 0 ? $leads->first() : null;

        if ($lead) {


            $this->pay->lead_id = $lead->id;
            $this->pay->contact_id = $contact->id;
            $this->pay->save();

            $result = Artisan::call('1c:pay-send '.$this->pay->id);

            $this->pay->status = $result;
            $this->pay->save();
        }
    }
}
