<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteRequest;
use App\Models\Site;
use App\Rules\SiteCheckTest;
use App\Services\amoCRM\Services\Site\SiteSend;

class SiteController extends Controller
{
    /**
     * @throws \Exception
     */
    public function create(SiteRequest $request)
    {
        $site = Site::query()->create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'course'  => $request->course,
            'product' => $request->product,
            'action'  => $request->action,
            'amount'  => $request->amount,
            'course_id' => $request->course_id,
            'body'    => $request->json(),
            'is_test' => SiteCheckTest::isTest($request),
            'status'  => 0,
        ]);
        exit;
        $result = SiteSend::send($site);

        $site->status = $result;
        $site->save();
    }
}
