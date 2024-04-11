<?php

namespace App\Http\Controllers;

use App\Jobs\OneCPay;
use App\Jobs\OneCPayUpdate;
use App\Models\Account;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Services\OneC\SendCourse;
use App\Services\amoCRM\Services\OneC\SendInstallment;
use App\Services\amoCRM\Services\OneC\SendReturn;
use App\Services\amoCRM\Services\OneC\SendSubscription;
use App\Services\amoCRM\Services\OneC\SendUpdate;
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
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function cron()
    {
        $pays = Pay::query()
            ->where('datetime', '>', '2024-04-8 16:00:00')
            ->where('status', 0)
            ->where('contact_id', null)
            ->limit(10)
            ->get();

        foreach ($pays as $pay) {

            $type = explode('.', $pay->code)[0];

            $action = $pay->action ?? 'create';

            $installment = preg_match_all("@\d{2}\.\d{2}.\d{2}-\d{2}\.\d{2}.\d{2}@", $pay->title);

            $actionName = "$type.$installment.$action.$pay->return";

            $amoApi = (new Client(Account::query()->first()))
                ->init()
                ->initLogs()
                ->initCache();

            $service = match ($actionName) {
                'course.1.create.0' => SendInstallment::class,  //рассрочка курса
                'course.0.create.0' => SendCourse::class,       //оплата курса

                'subscription.0.create.0' => SendCourse::class, //оплата подписки

                'course.1.create.1', 'subscription.0.create.1', 'course.0.create.1',
                'course.1.update.1', 'subscription.0.update.1', 'course.0.update.1' => SendReturn::class,

                'course.1.update.0', 'subscription.0.update.0', 'course.0.update.0' => SendUpdate::class,
            };

            $pay->action_name = $actionName;
            $pay->save();

            $service::run($amoApi, $pay);
        }
    }
}
