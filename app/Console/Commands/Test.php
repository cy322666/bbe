<?php

namespace App\Console\Commands;

use App\Jobs\Segment;
use App\Models\Account;
use App\Models\OneC\Pay;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Services\Site\SiteSend;
use Exception;
use HubSpot\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
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

    static string $uri = 'https://api.pachca.com/api/shared/v1';

    public int $tries = 1;

    /**
     * @throws Exception
     */
    public function handle()
    {
//<<<<<<< HEAD
//        $response = Http::post(static::$uri.'/clients' , [
//            'headers' => [
//                'Authorization' => 'Bearer lICGyFKZMlZSqbGsiEi6-2dC-jg75DzhbMzRRowvyuQ',
//            ]
//        ]);
//
//        dd($response->body());
//    }

//    public function backoff(): array
//    {
//        return [1, 5, 10];
//    }

    }

    private static function buildText(array $leadsArray): array
    {
//        $sale1 = []; $sale2 = [];
//=======
//        $amoApi = (new Client(Account::query()->first()))->init();
//
//        $hubspot = Factory::createWithAccessToken(env('HUBSPOT_TOKEN'));
//>>>>>>> 11ba89008f9d8e159f80152596727362fe46f39e
//
//        $response = $hubspot->apiRequest([
//            'path' => '/contacts/v1/lists/2370/contacts/all',
//            'qs' => [
//                'limit' => 20,
//                'after' => null,
//            ]
//        ]);

//        $response = json_decode($response->getBody()->getContents());
//
//        dd($response);

//        https://app.hubspot.com/contacts/4723027/lists/2370/filters
    }
}
