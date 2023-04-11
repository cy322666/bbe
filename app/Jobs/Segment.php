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

    private static int $pipelineId1 = 3342043; //главная
    private static int $pipelineId2 = 6540894; //теплая

    public int $tries = 1;
    /**
     * @throws Exception
     */
    public function handle()
    {
            $amoApi = (new Client(Account::query()->first()))->init();

            $amoApi->service->queries->setDelay(0.5);

            $lead = $amoApi
                ->service
                ->leads()
                ->find($this->segment->lead_id);

            if ($lead->contact !== null) {

                $contact = $lead->contact;

                $leads = $contact->leads->toArray();

                $leadsArray = [
                    'sale_pipeline1' => ['leads' => [], 'sale' => 0, 'active' => 0],
                    'sale_pipeline2' => ['leads' => [], 'sale' => 0, 'active' => 0],
                    'other'          => ['leads' => [], 'sale' => 0, 'active' => 0],
                    'count_leads'    => count($leads),
                ];

                foreach ($leads as $leadArray) {

                    $key = $leadArray['pipeline_id'] == static::$pipelineId1 ? 'sale_pipeline1' : null;

                    if (!$key) {

                        $key = $leadArray['pipeline_id'] == static::$pipelineId2 ? 'sale_pipeline2' : 'other';
                    }

                    $leadsArray[$key]['leads'][] = $leadArray['id'];

                    $leadsArray[$key]['sale'] += $leadArray['status_id'] == 142 ? $leadArray['sale'] : 0;
                    $leadsArray[$key]['active'] += $leadArray['status_id'] !== 142 ? 1 : 0;
                }
            }

            $this->segment->fill([
                'body'   => !empty($leadsArray) ? json_encode($leadsArray) : null,
                'sale'   => $leadsArray['sale_pipeline1']['sale'] + $leadsArray['sale_pipeline2']['sale'],
                'contact_id'  => !empty($contact) ? $contact->id : null,
                'status'      => 1,
                'count_leads' => !empty($leads) ? count($leads) : 1,
            ]);

            $text = implode("\n", static::buildText($leadsArray));

            $note = $lead->createNote(4);
            $note->text = $text ?? null;
            $note->element_type = 2;
            $note->element_id = $lead->id;
            $note->save();

            $this->segment->save();
    }

    private static function buildText(array $leadsArray): array
    {
        $sale1 = []; $sale2 = [];

        if (count($leadsArray['sale_pipeline1']['leads']) > 0) {

            $sale1 = array_merge([
                'Основная воронка :',
                'Активных : '.$leadsArray['sale_pipeline1']['active'],
                'Куплено на сумму : '.number_format($leadsArray['sale_pipeline1']['sale']),
                'Всего : '.count($leadsArray['sale_pipeline1']['leads']),
                '---------------------------',
            ], static::buildLinks($leadsArray['sale_pipeline1']['leads']));
        }

        if (count($leadsArray['sale_pipeline2']['leads']) > 0) {

            $sale2 = array_merge([
                'Теплая воронка :',
                'Активных : '.$leadsArray['sale_pipeline2']['active'],
                'Куплено на сумму : '.number_format($leadsArray['sale_pipeline2']['sale']),
                'Всего : '.count($leadsArray['sale_pipeline2']['leads']),
                '---------------------------',
            ], static::buildLinks($leadsArray['sale_pipeline2']['leads']));
        }

        return array_merge([
            'Сделки клиента',
            '---------------------------',
        ], $sale1, $sale2, [
            '---------------------------',
            'Всего сделок : '.$leadsArray['count_leads']
        ]);
    }


    private static function buildLinks($leadIds) : array
    {
        $leadsArray = [];

        foreach ($leadIds as $key => $leadId) {

            $leadsArray[] = 'https://bbeducation.amocrm.ru/leads/detail/'.$leadId;
        }

        return $leadsArray;
    }
}
