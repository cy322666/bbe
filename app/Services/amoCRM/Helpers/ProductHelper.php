<?php

namespace App\Services\amoCRM\Helpers;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

class ProductHelper
{
    static array $arrayProducts = [
        193 => [
            'Графический дизайнер',
        ],
        459 => [
            'Графический дизайнер Plus',
            'Графический дизайн Plus',
        ],
        208 => [
            'Иллюстратор'
        ],
        432 => [
            '2D-анимация'
        ],
        216 => [
            'UX/UI-дизайнер'
        ],
        444 => [
            'Дизайнер интерьеров'
        ],
        236 => [
            'Моушн-дизайнер',
        ],
        461 => [
            'Моушн-дизайнер Plus',
        ]
    ];

    public static function getProduct($name, $courseId) : ?Model
    {
        foreach (static::$arrayProducts as $productId => $productName) {

            if ($name == $productName)

                return Course::query()
                    ->where('course_id', $productId)
                    ->first();
        }

        return  Course::query()
            ->where('course_id', $courseId)
            ->first();
    }
}
