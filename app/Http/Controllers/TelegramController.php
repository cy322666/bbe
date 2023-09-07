<?php

namespace App\Http\Controllers;

use App\Models\TgProxy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class TelegramController extends Controller
{
    public function proxy(Request $request): RedirectResponse
    {
        Log::info(__METHOD__, [$request->server(), $request->all()]);

        foreach ($request->all() as $key => $value) {

            if ($value == 'test') {

                exit;
            }
        }

        TgProxy::query()->create([
            'referrer' => json_encode($request->server('HTTP_REFERER')),
            'body'     => json_encode($request->toArray()),
        ]);

        return Redirect::to('https://t.me/bbe_help_bot');
    }

    public function create(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        \App\Jobs\TgProxy::dispatch($request->toArray()['leads']['add'][0]['id']);
    }
}
