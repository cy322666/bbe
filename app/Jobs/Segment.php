<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Segment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public \App\Models\Segment $segment) {}

    //главная воронка
    private static int $pipleineId = 3342043;

    public int $tries = 3;
    /**
     * @throws Exception
     */
    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($this->segment->lead_id);

        if ($lead->contact !== null) {

            $contact = $lead->contact;

            $leads = $contact->leads->toArray();

            $leadsArray = [
                'sale_pipeline' => ['leads' => [], 'sale' => 0],
                'other'         => ['leads' => [], 'sale' => 0],
                'count_leads'   => count($leads),
            ];

            foreach ($leads as $leadArray) {

                $key = $leadArray['pipeline_id'] == static::$pipleineId ? 'sale_pipeline' : 'other';

                $leadsArray[$key]['leads'][] = $leadArray['id'];

                $leadsArray[$key]['sale'] += $leadArray['status_id'] == 142 ? $leadArray['sale'] : 0;
                $leadsArray['count_leads']++;
            }
//                $leadsArray[$lead['pipeline_id']] = $body;
//                $leadsArray[$lead['pipeline_id']]['ids'] = array_merge($leadsArray[$lead['pipeline_id']]['ids'], [$lead['id']]);
        }

        $this->segment->fill([
            'body'   => !empty($leadsArray) ? json_encode($leadsArray) : null,
            'sale'   => !empty($leadsArray) ? $leadsArray['sale_pipeline']['sale'] : 0,
            'contact_id'  => !empty($contact) ? $contact->id : null,
            'status'      => 1,
            'count_leads' => !empty($leads) ? count($leads) : 1,
        ]);
        $this->segment->save();

        $text = implode("\n", static::buildText($this->segment, $leadsArray['sale_pipeline']['leads']));

        $note = $lead->createNote(4);
        $note->text = $text ?? null;
        $note->element_type = 2;
        $note->element_id = $lead->id;
        $note->save();
    }

    public function backoff(): array
    {
        return [1, 5, 10];
    }

    private static function buildText(\App\Models\Segment $segment, $leadIds) : array
    {
        foreach ($leadIds as $key => $leadId) {

            $leadsArray[] = 'https://bbeducation.amocrm.ru/leads/detail/'.$leadId;
        }

        return array_merge([
            'Сделки клиента',
            '---------------------------',
            'Всего сделок : '.$segment->count_leads,
            'Куплено на сумму : '.number_format($segment->sale),
            '---------------------------',
            'Сделки Основной воронки :',
        ], $leadsArray ?? []);
    }
}
