<?php

namespace App\Http\Controllers;

use App\Jobs\ReturnLead;
use App\Models\Account;
use App\Models\TgProxy;
use App\Services\amoCRM\Client;
use App\Services\Telegram;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ToolsController extends Controller
{
    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function datePay(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['status'][0]['id'];

        $amoApi = (new Client(Account::query()->first()))->init();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($leadId);

        $lead->cf('Дата оплаты')->setDate(Carbon::now()->format('Y-m-d'));
        $lead->save();

        $method = $lead->cf('Способ оплаты')->getValue();

        if ($method == 'Лерна') {

            $chatId = env('TG_CHAT_LERNA');
            $token  = env('TG_TOKEN_LERNA');
        } else {
            $chatId = env('TG_CHAT_CURATOR');
            $token  = env('TG_TOKEN_CURATOR');
        }

        $start = $lead->cf('Дата старта потока')->getValue() ? Carbon::parse($lead->cf('Дата старта потока')->getValue())->format('Y-m-d') : '-';

        //отправка в чат с кураторами
        Telegram::send(implode("\n", [
                '*Успешная сделка!* ',
                '-----------------------',
                '*Продукт:*',
                'Название : '.$lead->cf('Название продукта')->getValue() ?? '-',
                'Тип : '.$lead->cf('Тип продукта')->getValue() ?? '-',
                'Дата старта потока : '.$start,
                'Гросс : '.$lead->sale,
                'Сумма nett : '.$lead->cf('Бюджет nett')->getValue() ?? '-',
                'Способ оплаты : '.$method,
                '*Клиент :* ',
                'Имя : '.$lead->contact->name ?? '-',
                'Почта контакта : '.$lead->contact->cf('Email')->getValue(),
                'Почта плательщика : '.$lead->cf('Почта плательщика')->getValue(),
                'Почта студента : '.$lead->cf('Почта студента (оплата)')->getValue() ?? '-',
            ]), $chatId, $token, [
                "text" => "Перейти в сделку",
                "url"  => "https://bbeducation.amocrm.ru/leads/detail/".$lead->id
            ]
        );
    }

    public function return(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['add'][0]['id'];

        ReturnLead::dispatch($leadId);
    }
}
