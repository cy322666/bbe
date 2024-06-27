<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Sla;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

        $sla = Sla::query()
            ->where('lead_id', $leadId)
            ->where('hook_1', '!=', null)
            ->first();

        if ($sla) {

            $sla->hook_2 = Carbon::now()
                ->timezone('Europe/Moscow')
                ->format('Y-m-d H:i:s');
            $sla->save();

            Artisan::call('sla:result', ['sla' => $sla->id]);
        }
    }
}
