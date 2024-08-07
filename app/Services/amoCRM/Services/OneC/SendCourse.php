<?php

namespace App\Services\amoCRM\Services\OneC;

use App\Console\Commands\PaySend;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use Ufee\Amo\Models\Lead;

class SendCourse
{
    /**
     * @throws \Exception
     *
     * ищем активную или успешную и крепим
     */
    public static function run(Client $amoApi, Pay $pay)
    {
        $contact = OneCService::searchContact($amoApi, $pay);

        if (empty($contact)) {

            $contact = Contacts::create($amoApi);
            $contact = Contacts::update($contact, ['Почта' => $pay->email]);
        } else {

            $lead = Leads::searchSuccessPay($contact, $amoApi, OneCService::ONE_PIPELINE_ID, $pay); //1 pipeline

            if (!$lead)
                $lead = Leads::searchSuccessPay($contact, $amoApi, OneCService::SOFT_PIPELINE_ID, $pay); //2 pipeline

            if (!$lead)
                $lead = Leads::searchSuccessPay($contact, $amoApi, OneCService::SNG_PIPELINE_ID, $pay); //3 pipeline

            if (!$lead)
                $lead  = Leads::searchPay($contact, $amoApi, [
                    OneCService::ONE_PIPELINE_ID,
                    OneCService::SOFT_PIPELINE_ID,
                    OneCService::SNG_PIPELINE_ID,
                ], $pay);
        }

        $pay->contact_id = $contact->id;
        $pay->lead_id = !empty($lead) && is_bool($lead) == false ? $lead->id : null;
        $pay->save();

        if (!empty($lead))

            PaySend::addPayWithLead($pay, $amoApi);
        else
            PaySend::addPayWithoutLead($pay, $amoApi);

        $pay->status = 1;
        $pay->save();

        OneCService::addNote($amoApi, $pay);
    }
}

//datetime > '2023-06-25 16:00:00.0' AND lead_id is null && contact_id is null

