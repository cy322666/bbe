<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Hubspot\Site;
use App\Models\Product;
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

    //TODO проверка на тест

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $site = Site::query()->find($this->argument('site'));

        $double = Site::query()
            ->where('id', '!=', $site->id)
            ->where('email', $site->email)
            ->orWhere('phone', $site->phone)
            ->where('created_at', '>', Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'))
            ->first();

        $course = Course::query()
            ->where('course_id', $site->courseid)
            ->first();

        if (!$course) {

            throw new \Exception('product not found');
        }

        if (!$double) {

            try {

                $contact = Contacts::search([
                    'Телефоны' => Contacts::clearPhone($site->phone),
                    'Почта'    => $site->email,
                ], $this->amoApi);

                if($site->is_test)

                    $statusId = 53757562;
                else
                    $statusId = '';//TODO match status for form id

                $productType = NoteHelper::getTypeProduct($site);

                if (!$contact) {

                    $contact = Contacts::create($this->amoApi, $site->firstname);
                    $contact = Contacts::update($contact, [
                        'Почта'    => $site->email,
                        'Телефоны' => [$site->phone],
                    ]);

                    $lead = Leads::create($contact, [
                        'status_id' => $statusId,
                        'sale'      => $site->amount,
                    ], $site->name);

                    $lead->cf('ID курса')->setValue($site->courseid);
                    $lead->cf('Название продукта')->setValue($site->coursename);
                    $lead->cf('url')->setValue($site->course_url);

                    if ($productType)
                        $lead->cf('Тип продукта')->setValue($productType);

                    $lead->attachTag($productType);

                    $lead = LeadHelper::setUtmsForObject($lead, $site);

//                    $lead->cf('Источник')->setValue('Основной сайт'); TODO match form id
                    $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($site->connect_method));
                    $lead->save();

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
                        'status_id' => $statusId,
                        'sale'      => $course->price,//TODO
                    ], $site->coursename);

                    if ($leadActive)

                        $lead->attachTag('В работе');

//                    $lead->cf('ID курса')->setValue($site->courseid);
//                    $lead->cf('Название продукта')->setValue($site->coursename);
//                    $lead->cf('url')->setValue($site->course_url);

//                    if ($productType)
//                        $lead->cf('Тип продукта')->setValue($productType);

//                    $lead->cf('Источник')->setValue('Основной сайт');
//                    $lead->cf('Способ оплаты')->setValue('Сайт');

                    $lead->cf('Способ связи')->setValue(NoteHelper::switchCommunication($site->connect_method));
                    $lead->attachTag($productType ?? null);

                    $lead = LeadHelper::setUtmsForObject($lead, $site);
                    $lead->save();
                }

//                $lead->attachTag('Основной');
                $lead->save();

                $site->lead_id = $lead->id;
                $site->contact_id = $contact->id;
                $site->save();

                Notes::addOne($lead, NoteHelper::createNoteHubspot($site));

            } catch (\Throwable $e) {

                $site->status = 5;
                $site->save();

                throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            }
        } else
            $site->status = 3;

        $site->save();
    }
}
