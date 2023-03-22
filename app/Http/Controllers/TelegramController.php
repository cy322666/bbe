<?php

namespace App\Http\Controllers;

use App\Models\TgProxy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function proxy(Request $request)
    {
        TgProxy::query()->create([
            'referrer' => $request->server('HTTP_REFERER'),
            'body'     => json_encode($request->toArray()),
        ]);
    }

    public function create(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $proxy = TgProxy::query()
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->where('status', 0)
            ->first();

        if ($proxy) {

            //TODO

        } else
            Log::warning(__METHOD__.' : PROXY NOT FOUND');
    }
}
