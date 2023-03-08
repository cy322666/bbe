<?php

namespace App\Http\Controllers;

use App\Jobs\OneCPay;
use App\Models\OneC\Pay;
use Illuminate\Http\Request;

class OneCController extends Controller
{
    public function pay(Request $request)
    {
        $pay = Pay::query()->create($request->toArray());

        OneCPay::dispatch($pay);
    }
}
