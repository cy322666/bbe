<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Product;
use App\Services\amoCRM\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    /**
     * @throws Exception
     */
    public function list()
    {
        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        //забираем из админки

//        $products = Http::get('https://bangbangeducation.ru/api/v4/courses')
//            ->object()
//            ->data;
//
//        foreach ($products as $product) {
//
//            Product::query()->updateOrCreate(
//                ['course_id' => $product->courseId],
//                ['name'      => $product->name->default],
//            );
//        }



        //забираем из амо

        $enums = $amoApi->service
            ->ajax()
            ->get('/api/v4/leads/custom_fields/692307', $data = [], $args = [])
            ->enums;

        dd($enums);
//
//        foreach ($enums as $element) {
//
//            Product::query()->updateOrCreate(
//                ['name' => $element->value],
//                [
//                    'field_enum_id' => $element->id,
//                    'sort' => $element->sort,
//                ]);
//        }

            //, 'field_enum_id', 'sort'

        $products = Product::query()
            ->select(['name'])
            ->where('field_name_id', null)
            ->get();

        foreach ($products as $product) {

            
        }

//            $amoApi->service
//                ->ajax()
//                ->patch('/api/v4/leads/custom_fields/692307', [
//                    'enums' => array_merge($enums, [
//                        +count($enums) => [
//                            'value' => $element['name'],
//                            'sort' => +count($enums),
//                        ]
//                    ]),
//                ], []);
    }
}
