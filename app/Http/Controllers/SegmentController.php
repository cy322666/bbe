<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function hook(Request $request)
    {
        $segment = Segment::query()->create([
            'lead_id'    => $request->leads[0]['add']['id'],
        ]);

        \App\Jobs\Segment::dispatch($segment);
    }
}
