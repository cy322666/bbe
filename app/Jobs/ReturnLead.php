<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\amoCRM\Client;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReturnLead implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $leadId) {}

    public function backoff(): array
    {
        return [10, 300, 500];
    }

    public function uniqueId(): string
    {
        return $this->leadId;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        Log::info(__METHOD__, ['lead_id' => $this->leadId]);

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs()
            ->initCache();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($this->leadId);

        if ($lead->contact !== null) {

            $leads = $lead->contact
                ->leads
                ->filter(function($lead) {

                    return $lead->status_id == 142 && $lead->pipeline_id == 3342043;
                });

            if ($leads->count() > 1 || $leads->count() == 1) {

                $leadPay = $leads->first();

                if ($leadPay->cf('Название продукта')->getValue()) {

                    Log::info(__METHOD__, [$leadPay->cf('Название продукта')->getValue()]);

                    $lead->cf('Название продукта')->setValue($leadPay->cf('Название продукта')->getValue());
                }

                $lead->cf('Сумма (получили)')->setValue($leadPay->cf('Бюджет nett')->getValue());
                $lead->save();

            } else
                $this->fail(new Exception('contact with no leads'));
                //след попытка
        } else
            $this->fail(new Exception('leads with no contacts'));
        //след попытка

        Log::info(__METHOD__.' lead_id '.$this->leadId. ' success');
    }
}
