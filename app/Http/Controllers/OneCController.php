<?php

namespace App\Http\Controllers;

use App\Jobs\OneCPay;
use App\Jobs\OneCPayUpdate;
use App\Models\OneC\Pay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class OneCController extends Controller
{
    public function pay(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        foreach ($request->Payments as $payment) {

            try {

                $pay = Pay::query()->where([
                    'order_id' => $payment['Order_ID'],
                    'code'     => $payment['Code']
                ])->first();

                $body = [
                    'datetime'  => Carbon::parse($payment['Date'])->format('Y-m-d H:i:s'),
                    'title'     => $payment['Сontract'],
                    'email'     => $payment['Mail'],
                    'code'      => $payment['Code'],
                    'sum'       => $payment['Sum'],
                    'sum_gross' => $payment['SumGross'],
                    'return'    => $payment['Return'],
                    'status'    => 0,
                    'payment_type'       => $payment['Payment_type'],
                    'installment_number' => $payment['Installment_number'],
                ];

                if (!$pay) {
                    $body = array_merge($body, [
                        'order_id'  => $payment['Order_ID'],
                        'number'    => $payment['Number'],
                    ]);

                    $pay = Pay::query()->create($body);

                    OneCPay::dispatch($pay)->delay(5);
                } else {

                    Pay::query()
                        ->where(['id' => $pay->id])
                        ->update($body);

                    OneCPayUpdate::dispatch($pay)->delay(5);
                }

            } catch (Throwable $e) {

                Log::error(__METHOD__, [$e->getMessage()]);

                continue;
            }

        }
    }
}
