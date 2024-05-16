<?php

namespace App\Services\amoCRM\Models;

use App\Services\amoCRM\Client;

abstract class Contacts extends Client
{
    public static function search($arrayFields, $client)
    {
        $contacts = false;

        if(key_exists('Телефон', $arrayFields))

            $phone = $arrayFields['Телефон'];

        elseif(key_exists('Телефоны', $arrayFields))

            $phone = $arrayFields['Телефоны'][0];

        if (!empty($phone)) {

            $contacts = $client->service
                ->contacts()
                ->searchByPhone(self::clearPhone($phone));
        }

        if (!$contacts || !$contacts->first()) {

            if(key_exists('Почта', $arrayFields)) {

                $contacts = $client->service
                    ->contacts()
                    ->searchByEmail($arrayFields['Почта']);
            }
        }

        if ($contacts !== null && $contacts->first() !== null) {
            return $contacts->first();
        }

        return null;
    }

    public static function update($contact, $arrayFields = [], $amoApi = null)
    {
        if(key_exists('Телефоны', $arrayFields)) {

            foreach ($arrayFields['Телефоны'] as $phone) {

                $contact->cf('Телефон')->setValue($phone);
            }
        }

        if(key_exists('Почта', $arrayFields)) {

            $contact->cf('Email')->setValue($arrayFields['Почта']);
        }

        if(key_exists('Ответственный', $arrayFields)) {

            $contact->responsible_user_id = $arrayFields['Ответственный'];
        }

        if(key_exists('Имя', $arrayFields)) {

            $contact->name = $arrayFields['Имя'];
        }

        if(key_exists('cf', $arrayFields)) {

            foreach ($arrayFields['cf'] as $fieldsName => $fieldValue) {

                if(strpos($fieldsName, 'Дата')) {

                    $contact->cf($fieldsName)->setData($fieldValue);
                }
                $contact->cf($fieldsName)->setValue($fieldValue);
            }
        }

        try {

            $contact->save();

        } catch (\Throwable $e) {

            if ($amoApi) {

                $contact = $amoApi->services->leads()->find($contact->id);

                return static::update($contact, $arrayFields);
            }
        }

        return $contact;
    }

    public static function create(Client $amoapi, $name = 'Неизвестно')
    {
        $contact = $amoapi->service
            ->contacts()
            ->create();

        $contact->name = $name;
        $contact->save();

        return $contact;
    }

    public static function get($client, $id)
    {
        return $client->service->contacts()->find($id);
    }

    public static function buildLink($amoApi, int $contactId) : string
    {
        return 'https://'.$amoApi->storage->model->subdomain.'.amocrm.ru/contacts/detail/'.$contactId;
    }

    public static function clearPhone(?string $phone): ?string
    {
        if ($phone) {

            return substr(str_replace([',', '(', ')', '-', '+', ' '],'', $phone), -10);
        }
        return null;
    }
}
