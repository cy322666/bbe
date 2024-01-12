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

                    $lead = Leads::create($contact, [
                        'status_id' => $site->is_test ? 53757562 : null,
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
