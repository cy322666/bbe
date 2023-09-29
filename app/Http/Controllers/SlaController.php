<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Sla;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    public function hook1(Request $request)
    {
        $leadId = $request->leads['add'][0]['id'] ?? $request->leads['status'][0]['id'];

        Sla::query()->updateOrCreate([
            'lead_id' => $leadId,
        ],[
            'hook_1'  => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }

    public function hook2(Request $request)
    {
        $leadId = $request->leads['add'][0]['id'] ?? $request->leads['status'][0]['id'];

        $sla = Sla::query()->updateOrCreate([
            'lead_id' => $leadId,
        ],[
            'hook_2'  => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        if ($sla->hook_1) {

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
