<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Course;
use App\Models\Hubspot\Broken;
use App\Models\Hubspot\Segment;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tags;
use App\Services\amoCRM\Services\Site\NoteHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendHubspotSegment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:send-segment-python';

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
        $amoApi = (new Client(Account::query()->first()))->init();

        $segments = Segment::query()
            ->where('created_at', '<', Carbon::now()->subDay())
            ->where('status', 0)
            ->where('is_double', null)
            ->limit(5)
            ->get();

        foreach ($segments as $segment) {

            $createLead = false;

            if (Broken::query()
                ->where('id', '!=', $segment->id)
                ->where('email', $segment->email)
                ->where('created_at', '>', Carbon::parse($segment->created_at)->subDays(3))//->format('Y-m-d H:i:s'))
                ->exists()) {

                $segment->is_double = true;
                $segment->status = 9;
                $segment->save();

                break;

            } else {

                try {
                    $course = $segment->courseid ?
                        Course::query()
                            ->where('course_id', 460)
                            ->first()
                        : null;

                    $contact = Contacts::search([
                        'Телефон' => Contacts::clearPhone($segment->phone),
                        'Почта' => $segment->email ?? null,
                    ], $amoApi);

                    if ($contact) {

                        $lead = Leads::search($contact, $amoApi, [
                            3342043,
                            6540894,
                            7206046,
                        ]);

                        if (!$lead)

                            $this->create($segment, $contact, null, $course);

                        else {

                            $lead = Leads::searchSuccess($contact, $amoApi, [
                                3342043,
                                6540894,
                                7206046,
                            ]);

                            if ($lead) {
                                //менее 1 d как закрыт в успех?
                                if (Carbon::parse($lead->closed_at)->addHour() >
                                    Carbon::now()->addHour()->subDay())

                                    $this->create($segment, $contact, null, $course);
                            }

                        }
                    }

                    $contact = Contacts::update($contact, [
                        'Почта' => $segment->email,
                        'Телефоны' => [$segment->phone],
                    ]);

                    $segment->lead_id = $lead->id ?? null;
                    $segment->contact_id = $contact->id;
                    $segment->status = 1;
                    $segment->save();


                } catch (\Throwable $e) {

                    throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function create($segment, $contact, $productType, $course)
    {
        try {
            $lead = Leads::create($contact, [
                'status_id' => $segment->is_test ? 53757562 : 55684270
            ], 'Новый лид заявка на демо-Python');

            if ($productType == null)
                $lead->attachTag('Годовая программа');
            else
                $lead->attachTag($productType);

            if ($course) {

                try {
                    $lead->cf('Название продукта')->setValue(trim($course->name));

                } catch (\Throwable $e) {
                    throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                }

                $lead->sale = $course->price;
                $lead->cf('Курсы (основное)')->setValue($course->name);

                $lead->sale = $course->price;
                $lead->cf('ID курса')->setValue($course->course_id);
                $lead->cf('url')->setValue($course->url);
            }
//            $lead->cf('Источник')->setValue('Брошенные корзины');
            $lead->save();

            Notes::add($lead, ['Заполнили форму на получение доступа к демо-курсу Python-разработчик 1 день назад']);

            Tags::add($lead, 'hubspot');

            return $lead;

        } catch (\Throwable $e) {

            throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }
    }
}
