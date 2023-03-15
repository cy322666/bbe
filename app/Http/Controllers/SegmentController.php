<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SegmentController extends Controller
{
    public function hook(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $segment = Segment::query()->create([
            'lead_id' => $request->toArray()['leads'][0]['add']['id'],
        ]);

        \App\Jobs\Segment::dispatch($segment);
    }
}
