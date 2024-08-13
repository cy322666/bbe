<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Hubspot\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\amoCRM\Services\Site\LeadHelper;
use App\Services\amoCRM\Services\Site\NoteHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendHubspotPopup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:send-popup {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public array $logic = [
        '72eb7983-5a90-4061-a92f-19304a9450ca' => [
            'name' => 'Заявка по спецофферу',
            'status_id' => 33522700,
            'source' => 'попап',
            'product' => null,
            'product_type' => 'Годовая программа',
            'note' => null,//Спецоффер
        ],
        'f9d36ace-016c-48a4-add8-1d383bd16d71' => [
            'name' => 'Подобрать обучение',
            'status_id' => 33522700,
            'source' => 'форма Подобрать обучение',
            'product' => null,
            'product_type' => null,
        ],
        '6a3bc6a0-a87f-4d0e-af8a-0695973d9b78' => [
            'name' => 'Заявка из статьи в Точке зрения',
            'status_id' => 33522700,
            'source' => 'Точка зрения',
            'product' => null,
            'product_type' => null,
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $this->amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initCache();

        $site = Site::query()->find($this->argument('site'));

        $site->is_double = $site->isDouble();

        if (!$site->is_double) {

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
                    $statusId = $this->logic[$site->form]['status_id'];

                $leadActive = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                    7206046,
                ]);

                $lead = Leads::create($contact, [
                    'status_id' => $statusId,
                    'sale'      => null,
                    'responsible_user_id' => 6103456,
                ], $this->logic[$site->form]['name']);

                $lead->cf('Источник')->setValue($this->logic[$site->form]['source']);
                $lead->cf('Тип продукта')->setValue($this->logic[$site->form]['product_type']);
                $lead->cf('form_id')->setValue($site->form);
                $lead = LeadHelper::setUtmsForObject($lead, $site);
                $lead->attachTags(['hubspot']);
                $lead->save();

                if ($site->form == 'f9d36ace-016c-48a4-add8-1d383bd16d71') {

                    $product = json_decode($site->body)?->consultation_request_category;

                    $lead->cf('Название продукта')->setValue($product);
                    $lead->save();

                    Notes::addOne($lead, 'Трафик из email-рассылки или СММ, интересующее направление: '.$product);
                }

                if ($site->form == 'f9d36ace-016c-48a4-add8-1d383bd16d71') {

                    $url = json_decode($site->body)?->url;

                    $lead->cf('url')->setValue($url);
                    $lead->save();
                }

                if ($leadActive)

                    Tasks::create($lead, [
                        'complete_till_at'    => time() + 60 + 60,
                        'responsible_user_id' => $lead->responsible_user_id,
                    ], 'Клиент оставил повторную заявку (хабспот)');

                $site->lead_id = $lead->id;
                $site->contact_id = $contact->id;
                $site->status = 1;
                $site->save();

            } catch (\Throwable $e) {

                throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            }
        } else
            $site->status = 3;

        $site->save();

        return CommandAlias::SUCCESS;
    }
}
