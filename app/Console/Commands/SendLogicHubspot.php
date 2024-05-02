<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Course;
use App\Models\Hubspot\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\amoCRM\Services\Site\LeadHelper;
use App\Services\amoCRM\Services\Site\NoteHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendLogicHubspot extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:send-logic {site}';

    public function handle()
    {
        //2f693ff5-0cb2-4ef6-981f-b1ebe544590f ИЛИ ec00fc1c-2c9f-4bcb-ad49-7695a786b624

        //proftest_start_education=сейчас

        $this->amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initCache();

        $site = Site::query()->find($this->argument('site'));

        foreach (json_decode($site->body) as $item) {

            if ($item->name == 'proftest_start_education' && $item->value == 'сейчас') {

                if ($site->is_test)

                    $statusId = 53757562;

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

                $leadActive = Leads::search($contact, $this->amoApi, [
                    3342043,
                    6540894,
                    7206046,
                    6237586,
                ]);

                if ($leadActive) {

                    Tasks::create($leadActive, [
                        'complete_till_at'    => time() + 60 + 60,
                        'responsible_user_id' => $leadActive->responsible_user_id,
                    ], 'Прошел профтест, горячий! + результат профтеста. Если не получается связаться с клиентом то запусти бота /Бот для Профтеста_ТЗ Ани');
                }

                $lead = Leads::create($contact, [
                    'status_id' => $statusId ?? 55684270,
                    'sale' => $course->price ?? null,
                    'responsible_user_id' => 6103456,
                ], $info['product'] ?? 'Новая заявка Hubspot');

                sleep(1);

                $lead = $this->amoApi->service->leads()->find($lead->id);

//                $lead->cf('ID курса')->setValue($info['course_id']);
                $lead->cf('url')->setValue($info['url'] ?? null);
                $lead->cf('form_id')->setValue($site->form);
                $lead->cf('Источник')->setValue('Сегмент (профтест)');

//                $lead->attachTags([$info['tag'], 'hubspot'], $productType ?? $info['type']);

                $lead = LeadHelper::setUtmsForObject($lead, $site);

//                try {
//                    $lead->cf('Название продукта')->setValue($course->name ?? $info['product']);
//
//                } catch (\Throwable) {}

                if (!empty($leadActive)) {

                    $lead->cf('Причина отказа')->setValue('Дубль');
                    $lead->status_id = 143;
                    $lead->save();
                } else {

                    Tasks::create($lead, [
                        'complete_till_at'    => time() + 60 + 60,
                        'responsible_user_id' => $lead->responsible_user_id,
                    ], 'Прошел профтест, горячий! + результат профтеста. Если не получается связаться с клиентом то запусти бота /Бот для Профтеста_ТЗ Ани');
                }

                $lead->save();

                $site->lead_id = $lead->id;
                $site->contact_id = $contact->id;
                $site->status = 1;
                $site->save();

            } else {

                $site->status = 1;
                $site->save();
            }
        }
    }
}
