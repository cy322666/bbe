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

    public int $tries = 2;

    public function __construct(public Pay $pay) {}

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $contactNoPayed = false;

        if ($this->pay->email)
            $contact = Contacts::search(['Почта' => $this->pay->email], $amoApi);

        if (empty($contact) && $this->pay->email) {

            $leads = $amoApi->service
                ->leads()
                ->searchByCustomField($this->pay->email, 'Почта плательщика');

            if ($leads->first()) {

                $contact = Contacts::search(['Почта' => $this->pay->email], $amoApi);

                if ($contact) {

                    $contactNoPayed = true;
                }
                $lead = $leads->first();
            }
        }

        if (empty($contact)) {

            $contact = Contacts::create($amoApi, 'Неизвестно');
            $contact = Contacts::update($contact, ['Почта' => $this->pay->email]);

            $this->pay->contact_id = $contact->id;
            $this->pay->status = 15;//новый контакт
            $this->pay->save();

        } elseif ($contactNoPayed) {

            $this->pay->lead_id = $lead->id;
            $this->pay->contact_id = $contact->id;
            $this->pay->status = 17;
            $this->pay->save();

        } else {

            $leads = $contact->leads->sortBy('created_at', 'DESC');

            if ($leads->count() > 0) {

                foreach ($leads as $lead) {

                    if ($lead->pipeline_id == 6362138 && $lead->status_id !== 142 && $this->pay->return) {

                        break;
                    }

                    if (($lead->status_id == 142 || $lead->status_id == 33524491) && $lead->pipeline_id == 3342043) {

                        //+ этап оплачивает
                        break;
                    }

                    if ($lead->status_id == 142 && $lead->pipeline_id == 6540894) {

                        break;
                    }

                    unset($lead);
                }
            }

            if (!empty($lead)) {

                $this->pay->lead_id = $lead->id;
                $this->pay->contact_id = $contact->id;
                $this->pay->status = 13;
                $this->pay->save();
            } else {

                $this->pay->contact_id = $contact->id;
                $this->pay->status = 11;
                $this->pay->save();
            }
        }

        Artisan::call('1c:pay-send '.$this->pay->id);
    }
}
