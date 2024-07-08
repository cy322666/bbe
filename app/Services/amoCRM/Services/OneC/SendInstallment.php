<?php

namespace App\Services\amoCRM\Services\OneC;

use App\Console\Commands\PaySend;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;

class SendInstallment
{
    public static function run(Client $amoApi, Pay $pay)
    {
        //смотрим предыдущие платежи - пока хз
        //ищем сделку с нужным курсом и крепим - смысл собрать платежи вместе
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
        $pay->lead_id = !empty($lead) ? $lead->id : null;
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
