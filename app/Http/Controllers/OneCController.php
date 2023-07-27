<?php

namespace App\Http\Controllers;

use App\Jobs\OneCPay;
use App\Jobs\OneCPayUpdate;
use App\Models\OneC\Pay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
                        'action'    => 'create',
                    ]);

                    Pay::query()->create($body);

                } else {

                    $pay = Pay::query()
                        ->where(['id' => $pay->id])
                        ->update($body);

                    $pay->action = 'update';
                    $pay->save();
                }

            } catch (Throwable $e) {

                Log::error(__METHOD__, [$e->getMessage()]);

                continue;
            }

        }
    }

    public function cron()
    {
        $pays = Pay::query()
            ->where('status', 0)
            ->where('check_id', null)
            ->limit(1)
            ->get();

        foreach ($pays as $pay) {

            if ($pay->action == ('create' || null)) {

                OneCPay::dispatch($pay);
            }
            if ($pay->action == 'update') {

                Artisan::call('1c:pay-update '.$pay->id);
            }
        }
    }
}
