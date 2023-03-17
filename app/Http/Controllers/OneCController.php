<?php

namespace App\Http\Controllers;

use App\Jobs\OneCPay;
use App\Models\OneC\Pay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OneCController extends Controller
{
    public function pay(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());
//        $pay = Pay::query()->create($request->toArray());

//        OneCPay::dispatch($pay);
    }
}
