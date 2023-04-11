<?php

namespace App\Http\Controllers;

use App\Jobs\OneCPay;
use App\Models\OneC\Pay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OneCController extends Controller
{
    public function pay(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        foreach ($request->Payments as $payment) {

            $pay = Pay::query()->create([
                'order_id'  => $payment['Order_ID'],
                'number'    => $payment['Number'],
                'datetime'  => Carbon::parse($payment['Date'])->format('Y-m-d H:i:s'),
                'title'     => $payment['Сontract'],
                'email'     => $payment['Mail'],
                'code'      => $payment['Code'],
                'sum'       => $payment['Sum'],
                'return'    => $payment['Return'],
                'status'    => 0,
                'payment_type' => $payment['Payment_type'],
            ]);

            OneCPay::dispatch($pay)->delay(5);
        }
    }
}
