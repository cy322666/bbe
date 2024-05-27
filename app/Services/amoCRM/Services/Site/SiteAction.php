<?php

namespace App\Services\amoCRM\Services\Site;

use App\Models\Course;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Helpers\ProductHelper;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
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

            $leadActive = false;

            $course = ProductHelper::getProduct($site->course, $site->course_id);

            $contact = Contacts::search([
                'Телефон' => Contacts::clearPhone($site->phone),
                'Почта'   => $site->email ?? null,
            ], $this->amoApi);

            $statusId = $site->is_test ? 53757562 : 33522700;

            $productType = NoteHelper::getTypeProduct($body);

            if (!$contact)
                $contact = Contacts::create($this->amoApi, $body->firstname);
            else
                $leadActive = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                    7206046,
                ]);

            $contact = Contacts::update($contact, [
                'Почта' => $site->email,
                'Телефоны' => [$site->phone],
            ]);

            $lead = Leads::create($contact, [
                'responsible_user_id' => 5998951,
                'status_id' => $statusId,
                'sale'      => $site->amount,
            ], $body->name);

            $lead->cf('registration')->setValue($body->registration ?? null);
            $lead->cf('lead_id')->setValue($body->lead_id ?? null);

            $lead->cf('ID курса')->setValue($site->course_id);
            $lead->cf('url')->setValue($body->url ?? null);

            if (!empty(json_decode($site->body)->url) && str_contains(json_decode($site->body)->url, 'sale.bangbangeducation.ru'))
                $lead->cf('Источник')->setValue('Лендинг Вебфлоу');
            else
                $lead->cf('Источник')->setValue('Основной сайт');

            $lead->cf('Способ оплаты')->setValue('Сайт');

            if ($course) {

                $lead->sale = $course->price;

                try {
                    $lead->cf('Название продукта')->setValue($course->name);
                } catch (Throwable $e) {}
            }

            if (!$course) {
                try {
                    $lead->cf('Название продукта')->setValue(trim($site->name));

                } catch (Throwable $e) {}
            }

            $productType ? $lead->cf('Тип продукта')->setValue($productType) : null;
            $productType ? $lead->attachTag($productType) : null;

            $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));

            $lead->attachTag('Основной');

            $lead = LeadHelper::setTariff($lead, $body);

            $lead->save();

            if ($leadActive) {
                //закрываем новую, активная - основная
//                $lead->cf('Причина отказа')->setValue('Дубль');
//                $lead->status_id = 143;
//                $lead->save();

                Tasks::create($lead, [
                    'complete_till_at'    => time() + 60 + 60,
                    'responsible_user_id' => $lead->responsible_user_id,
                ], 'Клиент оставил повторную заявку на консультацию');

                Notes::addOne($leadActive, NoteHelper::createNoteConsultation($body, $site));
            }

            $site->lead_id = $lead->id;
            $site->contact_id = $contact->id;
            $site->save();

            try {
                $lead = LeadHelper::setUtmsForObject($lead, $body);
                $lead->save();
            } catch (Throwable) {

                $lead = $this->amoApi->service->leads()->find($lead->id);

                $lead = LeadHelper::setUtmsForObject($lead, $body);
                $lead->save();
            }

            Notes::addOne($lead, NoteHelper::createNoteConsultation($body, $site));

        } catch (Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return 1;
    }
}
