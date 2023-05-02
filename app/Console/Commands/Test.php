<?php

namespace App\Console\Commands;

use App\Jobs\Segment;
use App\Models\Account;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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

        $pays = Pay::query()
//            ->where('id', '>', 219)
            ->where('id', '=', 228)
            ->get();

        foreach ($pays as $pay) {
            try {

                $result = Artisan::call('1c:pay-send '.$pay->id);

            } catch (\Throwable $e) {
                continue;
            }
            sleep(2);

            dump($pay->id);
        }
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
