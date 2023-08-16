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
                'Телефоны' => $site->phone,
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
                    'price'     => $body->price,
                ], $body->name);

                $lead = LeadHelper::setUtmsForObject($lead, $body);

                $lead->attachTag('Автооплата');

                try {

                    $lead->cf('Название продукта')->setValue($site->name);
                } catch (Exception $e) {

                    Telegram::send('Неизвестный продукт:'.$site->name, env('TG_CHAT_DEBUG'), env('TG_TOKEN_DEBUG'), []);
                }
                $lead->save();

            } else {

                $lead = Leads::search($contact, $this->amoApi);

                if (!$lead) {

                    $isActive = false;

                    foreach ($contact->leads as $leadTask) {

                        if ($leadTask->closest_task_at > time()) {

                            Notes::addOne($lead, NoteHelper::createNoteOrder());

                            Tasks::create($lead, [
                                'complete_till_at'    => time() + 60 + 60,
                                'responsible_user_id' => $leadTask->responsible_user_id,
                            ], $this->taskText);

                            $isActive = true;

                            break;
                        }
                    }

                    if ($isActive == false) {

                        $lead = Leads::create($contact, [
                            'status_id' => $site->is_test ? 53757562 : 142,
                            'price'     => $body->price,
                        ], $body->name);

                        $lead = LeadHelper::setUtmsForObject($lead, $body);

                        $lead->attachTag('Автооплата');

                        try {

                            $lead->cf('Название продукта')->setValue($site->name);
                        } catch (Exception $e) {

                            Telegram::send('Неизвестный продукт:'.$site->name, env('TG_CHAT_DEBUG'), env('TG_TOKEN_DEBUG'), []);
                        }
                        $lead->save();
                    }

                } else {
                    $lead = Leads::create($contact, [
                        'status_id' => $site->is_test ? 53757562 : 142,
                        'price'     => $site->amount,
                    ], $site->name);

                    $lead->attachTag('Автооплата');
                    $lead->attachTag($productType);

                    try {

                        $lead->cf('Название продукта')->setValue($site->name);
                    } catch (Exception $e) {

                        Telegram::send('Неизвестный продукт:'.$site->name, env('TG_CHAT_DEBUG'), env('TG_TOKEN_DEBUG'), []);
                    }
                    $lead->cf('Источник')->setValue('Основной сайт');
                    $lead->cf('Способ оплаты')->setValue('Сайт');
                    $lead->cf('Тип продукта')->setValue($productType);

                    $lead = LeadHelper::setUtmsForObject($lead, $body);
                }
            }

            $lead->save();
            $lead->attachTag('Основной');

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
