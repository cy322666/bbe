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

        $double = Site::query()
            ->where('id', '!=', $site->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'))
            ->where('lead_id', '!=', null)
            ->where('email', $site->email)
//            ->orWhere('phone', $site->phone)
            ->first();

        if ($site->courseid)
            $course = Course::query()
                ->where('course_id', $site->courseid)
                ->first();

        if (!$double) {

            $info = $site->prepareSend();

            try {

                $contact = Contacts::search([
                    'Телефон' => $site->phone,
                    'Почта'   => $site->email,
                ], $this->amoApi);

                if (!$contact) {
                    $contact = Contacts::create($this->amoApi, $site->firstname);
                    $contact = Contacts::update($contact, [
                        'Почта' => $site->email,
                        'Телефоны' => [$site->phone],
                    ]);
                }

                $lead = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                    7206046,
                ]);

                if ($lead)

                    $lead->attachTag('В работе');

                else {

                    if ($site->is_test) {

                        $statusId = 53757562;
                    } else {

                        foreach (static::$softForms as $form) {

                            if ($form == $site->form) {

                                $statusId = 33522700;

                                break;
                            }
                        }
                    }

                    $lead = Leads::create($contact, [
                        'status_id' => $statusId ?? null,
                        'sale'      => $course->price ?? null,
                        //TODO resp
                    ], $info['product'] ?? 'Новая заявка Hubspot');

                    try {
                        $lead->cf('Название продукта')->setValue($info['product']);

                    } catch (\Throwable) {}

                    $lead->cf('ID курса')->setValue($info['course_id']);
                    $lead->cf('url')->setValue($info['url']);
                    $lead->cf('form_id')->setValue($site->form);

                    $productType = NoteHelper::getTypeProduct($site);

                    if ($productType)
                        $lead->cf('Тип продукта')->setValue($info['type'] ?? $productType);

                    $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($site->connect_method));
                    $lead->cf('Источник')->setValue($info['source']);

                    $lead->attachTags([$info['tag'], 'hubspot'], $productType ?? $info['type']);

                    $lead = LeadHelper::setUtmsForObject($lead, $site);
                    $lead->save();
                }

                $site->lead_id = $lead->id;
                $site->contact_id = $contact->id;
                $site->status = 1;
                $site->save();

                Notes::addOne($lead, NoteHelper::createNoteHubspot($site, $info));

            } catch (\Throwable $e) {

                throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            }
        } else {
            $site->status = 3;
            $site->is_double = true;
        }

        $site->save();
    }
}
