<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Helpers\ProductHelper;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\amoCRM\Services\Site\LeadHelper;
use App\Services\amoCRM\Services\Site\NoteHelper;
use Illuminate\Console\Command;

class SendFlocktory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flocktory:send {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        try {
            $this->amoApi = (new Client(Account::query()->first()))
                ->init()
                ->initCache();

            $site = Site::query()->find($this->argument('site'));

            $leadActive = false;

//            $course = ProductHelper::getProduct($site->course, $site->course_id);

            $contact = Contacts::search([
                'Телефон' => $site->phone,
                'Почта'   => $site->email ?? null,
            ], $this->amoApi);

            $statusId = $site->is_test ? 53757562 : 55684270;

//            $productType = NoteHelper::getTypeProduct($body);

            if (!$contact)
                $contact = Contacts::create($this->amoApi, $site->body->name);
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
            ], 'Новая заявка с Флоктори');

//            $lead->cf('registration')->setValue($body->registration ?? null);
//            $lead->cf('lead_id')->setValue($body->lead_id ?? null);

//            $lead->cf('ID курса')->setValue($site->course_id);
//            $lead->cf('url')->setValue($body->url ?? null);

//            if (!empty(json_decode($site->body)->url) && str_contains(json_decode($site->body)->url, 'sale.bangbangeducation.ru'))
//                $lead->cf('Источник')->setValue('Лендинг Вебфлоу');
//            else

            $lead->cf('Источник')->setValue('Flocktory');
//
//            $lead->cf('Способ оплаты')->setValue('Сайт');

//            if ($course) {
//
//                $lead->sale = $course->price;
//
//                try {
//                    $lead->cf('Название продукта')->setValue($course->name);
//                } catch (Throwable $e) {}
//            }

//            if (!$course) {
//                try {
//                    $lead->cf('Название продукта')->setValue(trim($site->name));
//
//                } catch (Throwable $e) {}
//            }

//            $productType ? $lead->cf('Тип продукта')->setValue($productType) : null;
//            $productType ? $lead->attachTag($productType) : null;
//
//            $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($body->communicationMethod));

            $lead->attachTag('Флоктори');
            $lead->save();

//            $body->isLegal ? $lead->cf('isLegal')->enable() : null;
//            $lead->cf('companyName')->setValue($body->companyName ?? null);

//            $lead = LeadHelper::setTariff($lead, $body);

//            try {
//
//                $lead->save();
//            } catch (Throwable $e) {
//
//                sleep(5);
//
//                $lead->updated_at = time();
//                $lead->save();
//            }

            if ($leadActive) {
                //закрываем новую, активная - основная
//                $lead->cf('Причина отказа')->setValue('Дубль');
//                $lead->status_id = 143;
//                $lead->save();

                Tasks::create($lead, [
                    'complete_till_at'    => time() + 60 + 60,
                    'responsible_user_id' => $lead->responsible_user_id,
                ], 'Клиент оставил повторную заявку на Флоктори');

                Notes::addOne($leadActive, NoteHelper::createNoteFlocktory(json_decode($site->body), $site));
            }

            $site->lead_id = $lead->id;
            $site->contact_id = $contact->id;
            $site->save();

//            try {
////                $lead = LeadHelper::setUtmsForObject($lead, $body);
////                $lead->save();
//            } catch (Throwable) {
//
//                $lead = $this->amoApi->service->leads()->find($lead->id);
//
//                $lead = LeadHelper::setUtmsForObject($lead, $body);
//                $lead->save();
//            }

        } catch (\Throwable $e) {

            $site->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $site->save();

            throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }

        return Command::SUCCESS;
    }
}
