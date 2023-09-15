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
//            ->orWhere('phone', $request->phone)
            ->where('created_at', '>', Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))
            ->first();

        if (!$double) {

            $result = SiteSend::send($site);

            $site->status = $result;
        } else
            $site->status = 3;

        $site->save();
    }
}
