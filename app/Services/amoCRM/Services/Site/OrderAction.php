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

class OrderAction
{
    private string $taskText = 'Клиент оплатил на сайте, проверь и переведи в Успешно реализовано';

    public function __construct(public Client $amoApi) {}

    /**
     * @throws Exception
     */
    public function send(Site $site, object $body) : bool
    {
        try {

            if ($site->name == 'Подписка на год' ||
                $site->name == 'Подписка на месяц') exit;

            $productType = NoteHelper::getTypeProduct($body);

            $contact = Contacts::search([
                'Телефоны' => Contacts::clearPhone($site->phone),
                'Почта'    => $site->email ?? null,
            ], $this->amoApi);

            if (!$contact) {

                $contact = Contacts::create($this->amoApi, $body->firstname);
                $contact = Contacts::update($contact, [
                    'Почта'    => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $site->is_test ? 53757562 : 142,
                    'sale'      => $site->amount,
                ], $body->name);

                $lead = LeadHelper::setUtmsForObject($lead, $body);

                $lead->attachTag('Автооплата');

                $lead->cf('url')->setValue($body->url ?? null);
                $lead->cf('ID курса')->setValue($site->course_id);
                try {
                    if (!empty($body->months))
                        $lead->cf('')->setValue($body->months);

                    if (!empty($body->credit_price))
                        $lead->cf('')->setValue(preg_replace("/[^0-9]/", '', $body->credit_price));

                    if (!empty($body->course_tariff) && $body->course_tariff !== null) {

                        $lead->cf('Тариф')->setValue($body->course_tariff);
                    }
                    $lead->cf('Название продукта')->setValue(trim($site->name));
                } catch (Throwable $e) {}
                $lead->save();

            } else {

                $contact = Contacts::update($contact, [
                    'Почта'    => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                $lead = Leads::search($contact, $this->amoApi);

                if (!$lead) {

                    foreach ($contact->leads as $lead) {

                        if ($lead->closest_task_at > time()) {

                            break;
                        }
                    }
                }
            }

            if (empty($lead)) {

                $lead = Leads::create($contact, [
                    'status_id' => $site->is_test ? 53757562 : 142,
                    'sale'      => $site->amount,
                    'responsible_user_id' => 6103456,
                ], $body->name);


                if ($productType)
                    $lead->cf('Тип продукта')->setValue($productType);

                $lead->cf('url')->setValue($body->url ?? null);
                $lead->cf('ID курса')->setValue($site->course_id);
                $lead->cf('Способ оплаты')->setValue('Сайт (100%)');
                $lead->cf('Источник')->setValue('Сайт');

                try {
                    $lead->cf('Название продукта')->setValue(trim($site->name));

                    if (!empty($body->months))
                        $lead->cf('')->setValue($body->months);

                    if (!empty($body->credit_price))
                        $lead->cf('')->setValue(preg_replace("/[^0-9]/", '', $body->credit_price));

                } catch (Throwable $e) {}

                $lead->attachTag('Автооплата');
                $lead->save();

            } else
                Tasks::create($lead, [
                    'complete_till_at'    => time() + 60 + 60,
                    'responsible_user_id' => $lead->responsible_user_id,
                ], $this->taskText);

            $site->lead_id = $lead->id;
            $site->contact_id = $contact->id;
            $site->save();

            Notes::addOne($lead, NoteHelper::createNoteOrder($body, $site));

        } catch (Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return 1;
    }
}
