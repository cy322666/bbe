<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteRequest;
use App\Models\Site;
use App\Rules\SiteCheckTest;
use App\Services\amoCRM\Services\Site\SiteSend;
use Carbon\Carbon;

class SiteController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(SiteRequest $request)
    {
        $site = Site::query()->create([
            'name'    => $request->name ?? $request->course_name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'course'  => $request->course_name ?? null,
            'product' => $request->product_name_long ?? null,
            'action'  => $request->action,
            'amount'  => $request->amount,
            'course_id' => $request->course_id,
            'body'    => json_encode($request->all()),
            'is_test' => SiteCheckTest::isTest($request),
            'utm_term' => $request->utm_term,
            'utm_source' => $request->utm_source,
            'utm_medium' => $request->utm_medium,
            'utm_content' => $request->utm_content,
            'utm_campaign' => $request->utm_campaign,
            'status'  => 0,
        ]);

        $double = Site::query()
            ->where('id', '!=', $site->id)
            ->where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))
            ->first();

        if (!$double) {

            if (($site->amount == 0 || $site->amount == 1) && $site->name !== 'Консультация по каталогу') {

                $site->status = 7;

            } else {
                $result = SiteSend::send($site);

                $site->status = $result;
            }

        } else {

            if ($site->action !== 'order-received' && $site->action !== 'order') {

                $site->status = 3;
            }
        }

        $site->save();
    }

    public function cron()
    {
        exit;
        $sites = Site::query()
            ->where('created_at', '>', Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
            ->where('status', 0)
            ->where('error', '!=', null)
            ->where('lead_id', null)
            ->limit(10)
            ->get();

        foreach ($sites as $site) {

//            try {
                $result = SiteSend::send($site);
                $site->status = $result;
                $site->save();

//            } catch (\Throwable $e) {}
        }
    }
}
