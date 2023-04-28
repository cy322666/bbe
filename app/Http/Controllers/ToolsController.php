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

        //отправка в чат с кураторами
        Telegram::send(implode("\n", [
                '*Успешная сделка!* ',
                '*Продукт:*',
                'Название : '.$lead->cf('Название продукта')->getValue() ?? '-',
                'Тип : '.$lead->cf('Тип продукта')->getValue() ?? '-',
                'Дата начала : '.$lead->cf('Дата старта потока')->getValue() ?? '-',
                '*Клиент:* ',
                'Имя : '.$lead->contact->name ?? '-',
                'Почта : '.$lead->contact->cf('Email')->getValue() ?? '-',
            ]), env('TG_CHAT_CURATOR'), env('TG_TOKEN_CURATOR')
        );
    }

    public function return(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['add'][0]['id'];

        ReturnLead::dispatch($leadId);
    }
}
