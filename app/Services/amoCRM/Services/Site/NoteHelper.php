<?php

namespace App\Services\amoCRM\Services\Site;

abstract class NoteHelper
{
    public static function switchNoteType(string $action) : string
    {
//        return match ($action)
    }

    public static function createNoteDefault($data): string
    {
        $text = [
            'Новая заявка на сайте!',
            '-----------------------------',
            ' - Имя : '. $data->firstname ?? '-',
            ' - Почта : '. $data->email ?? '-',
            ' - Телефон : '. $data->phone ?? '-',
            '-----------------------------',
            ' - Название курса : '. $data->course_name ?? '-',
            ' - ID курса : '. $data->course_id ?? '-',
            '-----------------------------'
        ];
        return implode("\n", $text);
    }

    public static function createNoteOrder($data): string
    {
        $text = [
            'Новая оплата на сайте!',
            '-----------------------------',
            ' - Имя : '. $data->firstname ?? '-',
            ' - Почта : '. $data->email ?? '-',
            ' - Телефон : '. $data->phone ?? '-',
            ' - Оплачено : '. $data->amount ?? '-',
            '-----------------------------',
            ' - Название курса : '. $data->course_name ?? '-',
            ' - ID курса : '. $data->course_id ?? '-',
            '-----------------------------'
        ];
        return implode("\n", $text);
    }

    public static function createNoteConsultation($data): string
    {
        $text = [
            'Новая заявка на консультацию!',
            '-----------------------------',
            ' - Имя : '. $data['firstname'] ?? '-',
            ' - Почта : '. $data['email'] ?? '-',
            ' - Телефон : '. $data['phone'] ?? '-',
            '-----------------------------',
            ' - Название продукта : '. $data['coursename'] ?? '-',
            ' - Тип продукта : '. $data['coursetype'] ?? '-',
            ' - ID курса : '. $data['courseid'] ?? '-',
            '-----------------------------'
        ];

        if(!empty($data['communicationMethod']))
            $text = array_merge($text, [
                ' - Способ связи : '.self::switchCommunication($data['communicationMethod']),
            ]);

        return implode("\n", $text);
    }

    public static function switchCommunication($method): string
    {
        return match ($method) {
            'messenger' => 'Мессенджер',
            'phone' => 'Телефон',
            default => $method,
        };
    }

    public static function getTypeProduct($body) :? string
    {
        if (($body->discriminator && $body->discriminator == 'yearly-program') ||
            ($body->coursetype && $body->coursetype == 'yearly-program'))

            return 'Годовая программа';

        if (($body->discriminator && $body->discriminator == 'course') ||
            ($body->coursetype && $body->coursetype == 'course'))

        if ($body->product_name && $body->product_name == 'Подписка на год')

            return 'Подписка - 12 месяцев';
    }

    public static function createNoteCredit($data): string
    {
        $text = [
            'Новая рассрочка с сайта !',
            '-----------------------------',
            ' - Имя : '. $data->firstname ?? '-',
            ' - Почта : '. $data->email ?? '-',
            ' - Телефон : '. $data->phone ?? '-',
            ' - Оплачено : '. $data->amount ?? '-',
            '-----------------------------',
            ' - Название продукта : '. $data->coursename ?? '-',
            ' - Тип продукта : '. $data->coursetype ?? '-',
            ' - ID курса : '. $data->courseid ?? '-',
            '-----------------------------'
        ];
        return implode("\n", $text);
    }
}
