<?php

namespace App\Services\amoCRM\Services\Site;

use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\Telegram;
use Exception;
use Throwable;

class SiteAction
{
    private string $taskText = 'Клиент оставил заявку на сайте';

    public function __construct(public Client $amoApi) {}

    /**
     * @throws Exception
     */
    public function send(Site $site, object $body) : bool
    {
        try {

            $contact = Contacts::search([
                'Телефоны' => Contacts::clearPhone($site->phone),
                'Почта'    => $site->email ?? null,
            ], $this->amoApi);

            $statusId = $site->is_test ? 53757562 : 33522700;
            $statusId = !empty($body->feature) && $body->feature == 'subscription-3' ? 55684270 : $statusId;

            $productType = NoteHelper::getTypeProduct($body);

            if (!$contact) {

                $contact = Contacts::create($this->amoApi, $body->firstname);
                $contact = Contacts::update($contact, [
                    'Почта'    => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $statusId,
                    'sale'      => $site->amount,
                ], $body->name);

                $lead->cf('ID курса')->setValue($site->course_id);
                $lead->cf('url')->setValue($body->url ?? null);

                try {
                    if (!empty($body->course_tariff) && $body->course_tariff !== null) {

                        $lead->cf('Тариф')->setValue($body->course_tariff);
                    }
                    $lead->cf('Название продукта')->setValue(trim($site->name));
                } catch (Throwable $e) {}

                if ($productType)
                    $lead->cf('Тип продукта')->setValue($productType);

                $lead->attachTag($productType);

                $lead->cf('Источник')->setValue('Основной сайт');
                $lead->cf('Способ оплаты')->setValue('Сайт');

                if ($body->communicationMethod)
                    $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));

                if (!empty($body->months))
                    $lead->cf('Рассрочка Месяцы')->setValue($body->months);
//
//                if (!empty($body->credit_price)) {
//
//                    $price = preg_replace("/[^0-9]/", '', $body->credit_price);
//
//                    $lead->cf('Стоимость в месяц')->setValue();
//                    $lead->sale = $price * $body->months;
//                }
                $lead->save();

                $lead = LeadHelper::setUtmsForObject($lead, $body);

            } else {

                $contact = Contacts::update($contact, [
                    'Почта'    => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                $leadActive = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                    7206046,
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $statusId,
                    'sale'      => $site->amount,
                ], $body->name);

                if (!empty($body->course_tariff) && $body->course_tariff !== null) {

                    $lead->cf('Тариф')->setValue($body->course_tariff);
                }

                if ($leadActive)

                    $lead->attachTag('В работе');

                $lead->cf('ID курса')->setValue($site->course_id);
                $lead->cf('url')->setValue($body->url ?? null);
                try {
                    $lead->cf('Название продукта')->setValue(trim($site->name));
                } catch (Throwable $e) {}

                if ($productType)
                    $lead->cf('Тип продукта')->setValue($productType);

                $lead->cf('Источник')->setValue('Основной сайт');
                $lead->cf('Способ оплаты')->setValue('Сайт');

                if ($body->communicationMethod)
                    $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));

                if (!empty($body->months))
                    $lead->cf('Рассрочка Месяцы')->setValue($body->months);

                if (!empty($body->credit_price)) {

                    $price = preg_replace("/[^0-9]/", '', $body->credit_price);

                    $lead->cf('Стоимость в месяц')->setValue();
                    $lead->sale = $price * $body->months;
                }

                $lead->attachTag($productType ?? null);

                $lead = LeadHelper::setUtmsForObject($lead, $body);
                $lead->save();
            }

            $lead->attachTag('Основной');
            $lead->save();

            $site->lead_id = $lead->id;
            $site->contact_id = $contact->id;
            $site->save();

            Notes::addOne($lead, NoteHelper::createNoteConsultation($body, $site));

        } catch (Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return 1;
    }
}
