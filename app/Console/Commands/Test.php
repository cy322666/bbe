<?php

namespace App\Console\Commands;

use App\Jobs\Segment;
use App\Models\Account;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bbe:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private Client $amoApi;

    private static int $pipelineId1 = 3342043; //главная
    private static int $pipelineId2 = 6540894; //теплая

    public int $tries = 1;
    /**
     * @throws Exception
     */
    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $lead = $amoApi
            ->service
            ->leads()
            ->find(26484973);

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
                $leadsArray[$key]['active'] += $leadArray['status_id'] == 142 ? 1 : 0;
                $leadsArray['count_leads']++;
            }
//                $leadsArray[$lead['pipeline_id']] = $body;
//                $leadsArray[$lead['pipeline_id']]['ids'] = array_merge($leadsArray[$lead['pipeline_id']]['ids'], [$lead['id']]);
        }

        $text = implode("\n", static::buildText($leadsArray));

        dd($text);
    }

//    public function backoff(): array
//    {
//        return [1, 5, 10];
//    }

    private static function buildText(array $leadsArray): array
    {
        $sale1 = []; $sale2 = [];

        if (count($leadsArray['sale_pipeline1']['leads']) > 0) {

            $sale1 = array_merge([
                'Сделки Основной воронки :',
                'Активных : '.$leadsArray['sale_pipeline1']['active'],
                'Куплено на сумму : '.number_format($leadsArray['sale_pipeline1']['sale']),
                'Всего : '.count($leadsArray['sale_pipeline1']['leads']),
                '---------------------------',
            ], static::buildLinks($leadsArray['sale_pipeline1']['leads']));
        }

        if (count($leadsArray['sale_pipeline2']['leads']) > 0) {

            $sale2 = array_merge([
                'Сделки Теплой воронки :',
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
