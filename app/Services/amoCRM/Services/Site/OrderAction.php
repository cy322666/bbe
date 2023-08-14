<?php

namespace App\Services\amoCRM\Services\Site;

use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
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

            $contact = Contacts::search([
                'Телефоны' => $site->phone,
                'Почта'    => $site->email ?? null,
            ]);

            if (!$contact) {

                $contact = Contacts::create($this->amoApi);
                $contact = Contacts::update($contact, [
                    'Имя'      => $site->name,
                    'Почта'    => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $site->is_test ? 53757562 : 33522700,
                    'price'     => $body->price,
                ], $body->name);

                $lead->attachTag('Автооплата');
                $lead->save();

            } else {

                $lead = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                ]);

                if (!$lead) {

                    foreach ($contact->leads as $leadTask) {

                        if ($leadTask->closest_task_at > time()) {

                            Notes::addOne($lead, NoteHelper::createNoteOrder());

                            Tasks::create($lead, [
                                'complete_till_at'    => time() + 60 + 60,
                                'responsible_user_id' => $leadTask->responsible_user_id,
                            ], $this->taskText);

                            break;
                        }
                    }
                } else {
                    $lead->cf('Источник')->setValue('Основной сайт');
                    $lead->cf('Способ оплаты')->setValue('Сайт');
                    $lead->cf('Тип продукта')->setValue();

                    $productType = NoteHelper::getTypeProduct($body);

                    if ($productType) {

                        $lead->cf('Тип продукта')->setValue($productType);
                        $lead->attachTag($productType);
                    }

                    if ($body->communicationMethod) {

                        $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));
                    }

                    $lead = LeadHelper::setUtmsForObject($lead, $body);

                    $lead->attachTag('В работе');
                    $lead->save();
                }
            }

            $lead->attachTag('Основной');
            $lead->save();

            $site->lead_id = $lead->id;
            $site->contact_id = $contact->id;
            $site->save();

            NoteHelper::createNoteOrder(json_decode($body, true));

        } catch (Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return 1;
    }
}
