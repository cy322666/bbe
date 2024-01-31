<?php

namespace App\Services\amoCRM\Services\Site;

use App\Models\Course;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
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

            $course = $site->course_id ?
                Course::query()
                    ->where('course_id', $site->course_id)
                    ->first()
                : null;

            $contact = Contacts::search([
                'Телефон' => Contacts::clearPhone($site->phone),
                'Почта'   => $site->email ?? null,
            ], $this->amoApi);

            $statusId = $site->is_test ? 53757562 : 33522700;
//            $statusId = !empty($body->feature) && $body->feature == 'subscription-3' ? 55684270 : $statusId;

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

            if (empty($lead) && empty($leadActive))

                $lead = Leads::create($contact, [
                    'responsible_user_id' => $contact->responsible_user_id,
                    'status_id' => $statusId,
                    'sale'      => $site->amount,
                ], $body->name);

            $lead->cf('ID курса')->setValue($site->course_id);
            $lead->cf('url')->setValue($body->url ?? null);
            $lead->cf('Источник')->setValue('Основной сайт');
            $lead->cf('Способ оплаты')->setValue('Сайт');

            if ($course) {

                $lead->sale = $course->price;
                $lead->cf('Курсы (основное)')->setValue($course->name); //TODO
            }

            $productType ? $lead->cf('Тип продукта')->setValue($productType) : null;
            $productType ? $lead->attachTag($productType) : null;

            $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));

//            $leadActive ? $lead->attachTag('В работе') : null;
            $lead->attachTag('Основной');

            $lead = LeadHelper::setTariff($lead, $body);

            try {
                $lead->cf('Название продукта')->setValue(trim($site->name));

            } catch (Throwable $e) {}

            $lead->save();

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
