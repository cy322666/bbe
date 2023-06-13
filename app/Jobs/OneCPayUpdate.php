<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class OneCPayUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public Pay $pay) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('1c:pay-update '.$this->pay->id);
    }
}
