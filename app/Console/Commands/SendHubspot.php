<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Course;
use App\Models\Hubspot\Site;
use App\Models\Product;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\amoCRM\Services\Site\LeadHelper;
use App\Services\amoCRM\Services\Site\NoteHelper;
use App\Services\amoCRM\Services\Site\SiteSend;
use App\Services\Telegram;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendHubspot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:send {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    static array $softForms = [
        '3f4cc224-7bd6-4dbe-a52b-7a145c436d9e',
        'a226fb88-ccbf-49ed-a617-8f7e4fc02412',
        '36920db7-a258-4f06-bd36-c9385147e956',
        '6edfaf7d-39ff-4857-941e-e98b30fc3828',
        '4e034ae3-22e8-4212-9e03-9de547c97ad1',
        '9bd28df2-7a96-464d-b300-45d8a68d60ce',
        'bc127ef2-14df-4955-bb8c-900ad1e5dc10',
        '172fdf59-fdee-4a6f-aed2-9cec7f8bc4e6',

        '9be94507-6b84-441f-9648-9238fe26ff93',
        'c9115ddc-ea74-4428-a9f7-cf41f88fa93a',
        '8f64151d-f48a-40a7-a262-f2fc7ae27b8d',
    ];
    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $this->amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initCache();

        $site = Site::query()->find($this->argument('site'));

        $site->is_double = $site->isDouble();

        if ($site->courseid)
            $course = Course::query()
                ->where('course_id', $site->courseid)
                ->first();

        if (!$site->is_double) {

            $info = $site->prepareSend();

            try {

                $contact = Contacts::search([
                    'Телефон' => $site->phone,
                    'Почта'   => $site->email,
                ], $this->amoApi);

                if (!$contact)
                    $contact = Contacts::create($this->amoApi, $site->firstname ?? ' ');

                $contact = Contacts::update($contact, [
                    'Почта' => $site->email,
                    'Телефоны' => [$site->phone],
                ]);

                if ($site->is_test)

                    $statusId = 53757562;
                else
                    if (in_array($site->form, static::$softForms))

                        $statusId = 55684270;

                $leadActive = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                    7206046,
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $statusId ?? null,
                    'sale'      => $course->price ?? null,
                    'responsible_user_id' => 6103456,
                ], $info['product'] ?? 'Новая заявка Hubspot');

                //тип форм одинаковых
//                if ($site->type == 'cron3') {
//
//                    $lead->pipeline_id = 6540894;
//
//                    $lead->cf('Источник')->setValue('Форма Начать бесплатно');
//
//                    $lead->cf('Тип продукта')->setValue('Годовая программа');
//
//                } else {

                    if (!empty(json_decode($site->body)->url) && str_contains(json_decode($site->body)->url, 'sale.bangbangeducation.ru'))

                        $lead->cf('Источник')->setValue('Лендинг Вебфлоу');

                    if ($info['source'])
                        $lead->cf('Источник')->setValue($info['source']);

                    $productType = NoteHelper::getTypeProduct($site);

                    if ($productType)
                        $lead->cf('Тип продукта')->setValue(!empty($productType) ? $productType : $info['type']);
//                }

                try {
                    $lead->cf('Название продукта')->setValue($course->name ?? $info['product']);

                } catch (\Throwable) {}

                $lead->cf('ID курса')->setValue($info['course_id']);
                $lead->cf('url')->setValue($info['url']);
                $lead->cf('form_id')->setValue($site->form);

                $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($site->connect_method));

                $lead->attachTags([$info['tag'], 'hubspot'], $productType ?? $info['type']);

                $lead = LeadHelper::setUtmsForObject($lead, $site);
                $lead->save();

                if ($leadActive) {
                    //закрываем новую, активная - основная
//                    $lead->cf('Причина отказа')->setValue('Дубль');
//                    $lead->status_id = 143;
//                    $lead->save();

                    Tasks::create($lead, [
                        'complete_till_at'    => time() + 60 + 60,
                        'responsible_user_id' => $lead->responsible_user_id,
                    ], 'Клиент оставил повторную заявку (хабспот)');

                    Notes::addOne($lead, NoteHelper::createNoteHubspot($site, $info));
                }

                $site->lead_id = $lead->id;
                $site->contact_id = $contact->id;
                $site->status = 1;
                $site->save();

                Notes::addOne($lead, NoteHelper::createNoteHubspot($site, $info));

                if (!empty($info['note']))
                    Notes::addOne($lead, NoteHelper::createNoteHubspot($site, $info['note']));

            } catch (\Throwable $e) {

                throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            }
        } else
            $site->status = 3;

        $site->save();
    }
}
