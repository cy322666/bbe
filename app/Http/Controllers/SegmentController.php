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
            Log::info(__METHOD__, $request->toArray());

            $segment = Segment::query()->create([
                'lead_id' => $request->toArray()['leads']['add'][0]['id'],
            ]);

            \App\Jobs\Segment::dispatch($segment);

        } catch (Throwable $exception) {

            Log::error(__METHOD__,[$exception->getMessage().' '.$exception->getFile().' '.$exception->getLine()]);
        }
    }
}
