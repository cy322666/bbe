<?php

namespace App\Services\amoCRM\Services\Site;

use App\Models\Course;
use App\Models\Log;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Helpers\ProductHelper;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\Telegram;
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
                'Телефон'  => Contacts::clearPhone($site->phone),
                'Почта'    => $site->email ?? null,
            ], $this->amoApi);

            $productType = NoteHelper::getTypeProduct($body);

            $course = ProductHelper::getProduct($site->course, $site->course_id);

            if (!$contact) {

                $contact = Contacts::create($this->amoApi, $body->firstname);
                $contact = Contacts::update($contact, [
                    'Почта'    => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $site->is_test ? 53757562 : 33522700,
                    'sale'      => $site->amount,
                    'responsible_user_id' => 5998951,
                ], $body->name);

                $lead->cf('registration')->setValue($body->registration ?? null);
                $lead->cf('lead_id')->setValue($body->lead_id ?? null);
                $lead->cf('url')->setValue($body->url ?? null);

                $lead = LeadHelper::setTariff($lead, $body);

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
                    'status_id' => $site->is_test ? 53757562 : 33522700,
                    'sale'      => $site->amount,
                    'responsible_user_id' => $contact->responsible_user_id,
                ], $body->name);
            }

            $lead->cf('ID курса')->setValue($site->course_id);
            $lead->cf('url')->setValue($body->url ?? null);

            $lead = LeadHelper::setTariff($lead, $body);

            if ($productType) {
                $lead->cf('Тип продукта')->setValue($productType);
            }

            $lead->cf('Источник')->setValue('Основной сайт');
            $lead->cf('Способ оплаты')->setValue('Сайт');

            $lead->attachTag($productType ?? null);

            if ($body->communicationMethod) {
                $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));
            }

            $lead = LeadHelper::setUtmsForObject($lead, $body);

            if ($course) {

                $lead->sale = $course->price;

                try {
                    $lead->cf('Название продукта')->setValue($course->name);
                } catch (Throwable) {
                    throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                }
            }
            $lead->save();

            if (!empty($leadActive)) {
                //закрываем новую, активная - основная
                $lead->cf('Причина отказа')->setValue('Дубль');
                $lead->status_id = 143;
                $lead->save();

                Tasks::create($lead, [
                    'complete_till_at'    => time() + 60 + 60,
                    'responsible_user_id' => $lead->responsible_user_id,
                ], 'Клиент оставил повторную заявку на рассрочку');

                Notes::addOne($leadActive, NoteHelper::createNoteCredit($body, $site));
            }

            Notes::addOne($lead, NoteHelper::createNoteCredit($body, $site));

            $site->lead_id = $lead->id ?? null;
            $site->contact_id = $contact->id;
            $site->save();

        } catch (Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return 1;
    }
}
