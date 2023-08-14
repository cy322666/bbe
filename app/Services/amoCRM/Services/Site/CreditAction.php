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

class CreditAction
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

            } else {

                $lead = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                ]);

                if ($lead) {

                    $lead->attachTag('В работе');
                    $lead->save();

                } else {

                    $productType = NoteHelper::getTypeProduct($body);

                    if ($productType) {

                        $lead->cf('Тип продукта')->setValue($productType);
                        $lead->attachTag($productType);
                    }

                    $lead->cf('Источник')->setValue('Основной сайт');
                    $lead->cf('Способ оплаты')->setValue('Сайт');
                    $lead->cf('Тип продукта')->setValue();
                    $lead->save();

                    if ($body->communicationMethod) {

                        $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));
                    }

                    $lead = LeadHelper::setUtmsForObject($lead, $body);
                }
            }

            $lead->attachTag('Основной');
            $lead->save();

            $site->lead_id = $lead->id;
            $site->contact_id = $contact->id;
            $site->save();

            NoteHelper::createNoteCredit(json_decode($body, true));

        } catch (Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return 1;
    }
}
