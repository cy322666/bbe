<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\TgProxy;
use App\Services\amoCRM\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ToolsController extends Controller
{
    public function datePay(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['status'][0]['id'];

        $amoApi = (new Client(Account::query()->first()))->init();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($leadId);

        $lead->cf('Дата оплаты')->setDate('Y-m-d');
        $lead->save();
    }
}
