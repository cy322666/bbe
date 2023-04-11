<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TgProxy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public int $leadId) {}

    public function handle()
    {
        try {
            $amoApi = (new Client(Account::query()->first()))->init();

            $amoApi->service->queries->setDelay(0.5);

            $proxy = \App\Models\TgProxy::query()
                ->where('created_at', '>', Carbon::now()->subMinutes(15))
                ->where('status', 0)
                ->first();

            if ($proxy) {

                $lead = $amoApi->service
                    ->leads()
                    ->find($this->leadId);

                $body = json_decode($proxy->body);

                $lead->cf('utm_source')->setValue($body->utm_source ?? null);
                $lead->cf('utm_medium')->setValue($body->utm_medium ?? null);
                $lead->cf('utm_term')->setValue($body->utm_term ?? null);
                $lead->cf('utm_content')->setValue($body->utm_content ?? null);
                $lead->cf('utm_campaign')->setValue($body->utm_campaign ?? null);
                $lead->save();

                $proxy->lead_id = $this->leadId;
                $proxy->contact_id = $lead->contact->id ?? null;
                $proxy->status = 1;

            } else
                Log::warning(__METHOD__.' : PROXY NOT FOUND');

        } catch (\Throwable $exception) {

            $proxy->error = $exception->getMessage().' '.$exception->getFile().' '.$exception->getLine();

        } finally {
            if (!empty($proxy)) {

                $proxy->save();
            }
        }
    }
}
