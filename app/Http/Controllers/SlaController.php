<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Sla;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlaController extends Controller
{
    public function hook1(Request $request)
    {
        $leadId = $request->leads['add'][0]['id'] ?? $request->leads['status'][0]['id'];

        Sla::query()->updateOrCreate([
            'lead_id' => $leadId,
        ], [
            'hook_1'  => Carbon::now()
                ->timezone('Europe/Moscow')
                ->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * @throws Exception
     */
    public function hook2(Request $request)
    {
        $leadId = $request->leads['add'][0]['id'] ?? $request->leads['status'][0]['id'];

        $sla = Sla::query()->updateOrCreate([
            'lead_id' => $leadId,
        ], [
            'hook_2'  => Carbon::now()
                ->timezone('Europe/Moscow')
                ->format('Y-m-d H:i:s')
        ]);

        if ($sla->hook_1) {

            $checkPeriod = Sla::query()
                ->where('id', $sla->id)
                ->whereBetween('hook_1', [
                    '10:00:00',
                    '19:00:00',
                ])
                ->whereBetween('hook_2', [
                    '10:00:00',
                    '19:00:00',
                ])->exists();

            if ($checkPeriod === false) {

                Log::warning(__METHOD__.' sla : '.$sla->id.' not in range');

                exit;
            }

            $hook1 = Carbon::parse($sla->hook_1);
            $hook2 = Carbon::parse($sla->hook_2);

            $sla->fill([
                'time_minutes' => $hook1->diffInMinutes($hook2),
                'time_seconds' => $hook1->diffInSeconds($hook2),
            ]);
            $sla->save();

            $amoApi = (new Client(Account::query()->first()))
                ->init()
                ->initCache();

            $lead = $amoApi->service->leads()->find($leadId);

            $lead->cf('SLA')->setValue($sla->time_minutes);
            $lead->save();
        }
    }
}
