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
            'referrer' => json_encode($request->server('HTTP_REFERER')),
            'body'     => json_encode($request->toArray()),
        ]);
    }

    public function create(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        \App\Jobs\TgProxy::dispatch($request);
    }
}
