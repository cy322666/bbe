<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SegmentController extends Controller
{
    public function hook(Request $request)
    {
        try {
            exit;
            Log::info(__METHOD__, $request->toArray());

            $leadId = $request->toArray()['leads']['add'][0]['id'];

            if (!Segment::query()->where('lead_id', $leadId)->exists()) {

                $segment = Segment::query()->create([
                    'lead_id' => $leadId,
                    'status'  => 0,
                ]);

                \App\Jobs\Segment::dispatch($segment)->delay(10);
            }

        } catch (Throwable $exception) {

            Log::error(__METHOD__,[$exception->getMessage().' '.$exception->getFile().' '.$exception->getLine()]);
        }
    }
}
