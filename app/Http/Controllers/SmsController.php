<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Sms;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Notes;
use App\Services\TargetSMS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    /**
     * @throws \Exception
     */
    public function agreement(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $authCode = new TargetSMS(
            env('TARGET_LOGIN'),
            env('TARGET_PASSWORD')
        );

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        $leadId = $request->toArray()['leads']['status'][0]['id'];

        $lead = $amoApi->service->leads()->find($leadId);

        $contact = $lead->contact;

        $phone = $lead->cf('Телефон')->getValue();

        $text = 'Вот ваш договор '.$lead->cf('Договор. Ссылка')->getValue().'. Вот код подтверждения: {код}. Подтвердите введя его тут '.$lead->cf('Договор. Анкета код')->getValue();

        $result = $authCode->generateCode(
            $phone,
            env('TARGET_SENDER'),
            4,
            $text,
        );
        $code   = $result['xml']->success->attributes()['code'];
        $idSms  = $result['xml']->success->attributes()['id_sms'];
        $status = $result['xml']->success->attributes()['status'];

        Sms::query()->create([
            'id_sms' => $idSms,
            'status' => $status,
            'info'   => $result['info'],
            'result' => $result['result'],
            'error'  => $result['error'],
            'phone'  => $phone,
            'lead_id' => $lead->id,
            'send_code'  => $code,
            'contact_id' => $contact->id,
        ]);

        Notes::addOne($lead, $text);

        $lead->status_id = 59740474; //код отправлен
        $lead->save();
    }

    /**
     * @throws \Exception
     */
    public function check(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        $leadId = $request->toArray()['leads']['status'][0]['id'];

        $lead = $amoApi->service->leads()->find($leadId);

        $sms = Sms::query()
            ->where('lead_id', $leadId)
            ->first();

        $sms->get_code = $lead->cf('Код подтверждения')->getValue();
        $sms->is_agreement = $sms->get_code == $sms->send_code;
        $sms->save();

        if ($sms->is_agreement) {

            $lead->status_id = 142;
            $lead->save();

            Notes::addOne($lead, 'Коды подтверждения совпадают : '.$sms->send_sms.' > '.$sms->get_sms);
        } else
            Notes::addOne($lead, 'Коды подтверждения не совпадают : '.$sms->send_sms.' > '.$sms->get_sms);
    }
}
