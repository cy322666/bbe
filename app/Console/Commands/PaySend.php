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
    public function handle(): int
    {
        $pay = Pay::query()->find($this->argument('pay'));

        $amoApi = (new Client(Account::query()->first()))->init();

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
                            "entity_id"   => (int)$pay->contact_id,
                            "entity_type" => "contacts",
                        ]
                    ]],
                ],
                [
                    "field_id" => 692289,
                    "values"   => [[
                        "value"   => Carbon::parse($pay->datetime)->timestamp,
                    ]],
                ], [
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
            ]
        ]];

        $check = $amoApi
            ->service
            ->ajax()->postJson('/api/v4/catalogs/6945/elements', $data, []);

        if ($check) {

            $checkId = $check->_embedded->elements[0]->id;

            $pay->check_id = $checkId;
            $pay->save();

            $amoApi->service
                ->ajax()->post('/api/v4/leads/'.$pay->lead_id.'/link', [[
                    "to_entity_id"   => $checkId,
                    "to_entity_type" => "catalog_elements",
                    "metadata" => [
                        "quantity"   => 1,
                        "catalog_id" => 6945
                    ]
                ]], [], 'json');

            $sum = Pay::query()
                ->where('lead_id', $pay->lead_id)
                ->sum('sum');

            $lead = $amoApi->service
                ->leads()
                ->find($pay->lead_id);

            if ($lead->sale == $sum) {

                $duty = 0;
                $full = 'Да';
            } else {
                $duty = $lead->sale - $sum;
                $full = 'Нет';
            }

            $lead->cf('Долг')->setValue($duty);
            $lead->cf('Оплачено полностью')->setValue($full);
            $lead->save();
        }

        return 1;
    }
}
