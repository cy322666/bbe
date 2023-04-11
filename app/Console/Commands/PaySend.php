<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class PaySend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1c:pay-send {pay}';

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
    public function handle()
    {
        $pay = Pay::query()->find($this->argument('pay'));

        $amoApi = (new Client(Account::query()->first()))->init();

        Log::info(__METHOD__, [
            [
                "field_id" => 692281,
                "values"   => [[
                    "value"   => 'Оплачен',
                    "enum_id" => 788455,
                    "enum_code" => "paid",
                ]],
            ], [
                "field_id" => 692285,
                "values"   => [[
                    "value"   => [
                        "entity_id"   => $pay->contact_id,
                        "entity_type" => "contacts",
                    ]
                ]],
            ], [
                "field_id" => 692289,
                "values"   => [[
                    "value"   => Carbon::parse($pay->datetime)->timestamp,
                ]],
            ], [
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
                        "external_uid"   => $pay->order_id,
                    ],
                ]],
            ], [
                "field_id" => 692293,
                "values"   => [[
                    "value"   => $pay->email,
                ]],
            ], [
                "field_id" => 692295,
                "values"   => [["value"   => 1]],
            ],
            [
                "field_id" => 694885,
                "values"   => [["value"   => $pay->return == true ? 'Да' : 'Нет']],
            ],
        ]);

        $data = [[
            "name" => 'Оплата №'.$pay->number,
            "custom_fields_values" => [
                [
                    "field_id" => 692281,
                    "values"   => [[
                        "value"   => 'Оплачен',
                        "enum_id" => 788455,
                        "enum_code" => "paid",
                    ]],
                ], [
                    "field_id" => 692285,
                    "values"   => [[
                        "value"   => [
                            "entity_id"   => $pay->contact_id,
                            "entity_type" => "contacts",
                        ]
                    ]],
                ], [
                    "field_id" => 692289,
                    "values"   => [[
                        "value"   => Carbon::parse($pay->datetime)->timestamp,
                    ]],
                ], [
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
                            "external_uid"   => $pay->order_id,
                        ],
                    ]],
                ], [
                    "field_id" => 692293,
                    "values"   => [[
                        "value"   => $pay->email,
                    ]],
                ], [
                    "field_id" => 692295,
                    "values"   => [["value"   => 1]],
                ],
                [
                    "field_id" => 694885,
                    "values"   => [["value"   => $pay->return == true ? 'Да' : 'Нет']],
                ],
            ]
        ]];

        $check = $amoApi
            ->service
            ->ajax()->postJson('/api/v4/catalogs/6945/elements', $data, []);

        if ($check) {

            $checkId = $check->_embedded->elements[0]->id;

            Log::info(__METHOD__.' '.$checkId);

            $pay->check_id = $checkId;
            $pay->save();

            $amoApi
                ->service
                ->ajax()->post('/api/v4/leads/'.$pay->lead_id.'/link', [[
                    "to_entity_id"   => $checkId,
                    "to_entity_type" => "catalog_elements",
                    "metadata" => [
                        "quantity"   => 1,
                        "catalog_id" => 6945
                    ]
                ]], [], 'json');

            return CommandAlias::SUCCESS;
        } else
            return CommandAlias::FAILURE;
    }
}
