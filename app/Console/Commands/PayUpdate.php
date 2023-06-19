<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class PayUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1c:pay-update {pay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle(): int
    {
        Log::info(__METHOD__.' pay : '.$this->argument('pay'));

        $amoApi = (new Client(Account::query()->first()))->init();

        $pay = Pay::query()->find($this->argument('pay'));

        if (!$pay->check_id) return 1;

        $data = [
            [
                "field_id" => 695240,
                "values"   => [[
                    "value"   => $pay->installment_number,
                ]],
            ],
            [
                "field_id" => 692291,
                "values"   => [[
                    "value"   => [
                        'sku' => $pay->code,
                        'description'    => $pay->title,
                        'unit_price'     => $pay->sum,
                        "quantity"       => 1,
                        "unit_type"      => "шт.",
                        'vat_rate_value' => 20,
                        "vat_rate_id"    => 0,
                        "external_uid"   => (string)$pay->order_id,
                    ],
                ]],
            ], [
                "field_id" => 692293,
                "values"   => [[
                    "value"   => $pay->email,
                ]],
            ], [
                "field_id" => 692295,
                "values"   => [["value" => $pay->sum]],
            ],
            [
                "field_id" => 694821,
                "values"   => [["value" => $pay->payment_type]],
            ],
            [
                "field_id" => 694885,
                "values"   => [["value" => $pay->return == true ? 'Да' : 'Нет']],
            ],
            [
                "field_id" => 695851,
                "values"   => [["value" => 'Да']],
            ]
        ];

        $amoApi
            ->service
            ->ajax()
            ->patch('/api/v4/catalogs/6945/elements', [
                [
                    'id' => (int)$pay->check_id,
                    "custom_fields_values" => $data
                ]
            ]);

        return CommandAlias::SUCCESS;
    }
}
