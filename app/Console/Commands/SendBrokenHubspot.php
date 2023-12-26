<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Course;
use App\Models\Hubspot\Broken;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Tags;
use App\Services\amoCRM\Services\Site\NoteHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBrokenHubspot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:send-broken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $brokens = Broken::query()
            ->where('created_at', '<', Carbon::now()->subHours(2))
            ->where('status', 0)
            ->where('is_double', false)
            ->limit(10)
            ->get();

        foreach ($brokens as $broken) {

            if (Broken::query()
                ->where('id', '!=', $broken->id)
                ->where('email', $broken->email)
                ->where('created_at', '>', Carbon::now()->subMinutes(30))//->format('Y-m-d H:i:s')
                ->exists()) {
                $broken->is_double = true;
                $broken->save();
            } else {
                try {

                    $course = $broken->courseid ?
                        Course::query()
                            ->where('course_id', $broken->courseid)
                            ->first()
                        : null;

                    $contact = Contacts::search([
                        'Телефон' => Contacts::clearPhone($broken->phone),
                        'Почта' => $broken->email ?? null,
                    ], $this->amoApi);

                    $statusId = $broken->is_test ? 53757562 : 33522700;

                    $productType = NoteHelper::getTypeProduct($broken);

                    if (!$contact) {

                        $contact = Contacts::create($amoApi, $broken->firstname);

                        $contact = Contacts::update($contact, [
                            'Почта' => $broken->email,
                            'Телефоны' => [$broken->phone],
                        ]);

                    } else {
                        //поиск в работе в основной
                        $leadActive = Leads::search($contact, $amoApi, [
                            3342043,
//                            6540894,
//                            7206046,
                        ]);

                        if ($leadActive) {
                            //push manager bot

                            try {
                                $amoApi->service->salesbots()->start(15543, $leadActive->id);

                            } catch (\Throwable $e) {

                                Log::info(__METHOD__, [$e->getMessage(), $e->getTraceAsString()]);
                            }


//                        } else {
                            //поиск успешной везде по дате создания, пока стоп
//                            $leadSuccess = Leads::searchSuccess($contact, $amoApi, [
//                                3342043,
////                                6540894,
////                                7206046,
//                            ]);
//
//                            if ($leadSuccess) {}
                        }
                    }

                    $lead = Leads::create($contact, [], 'Новый лид брошенная корзина');

                    $productType ? $lead->cf('Тип продукта')->setValue($productType) : null;
                    $productType ? $lead->attachTag($productType) : null;

                    try {
                        $lead->cf('Название продукта')->setValue(trim($broken->coursename));

                    } catch (\Throwable $e) {}

                    if ($course) {

                        $lead->sale = $course->price;
                        $lead->cf('Курсы (основное)')->setValue($course->name); //TODO
                    }

                    $lead->cf('ID курса')->setValue($broken->courseid);
                    $lead->cf('url')->setValue($broken->course_url);
                    $lead->cf('Источник')->setValue('Брошенные корзины');
                    $lead->save();

                    Tags::add($lead, 'БрошеннаяКорзина');

                    $broken->lead_id = $lead->id;
                    $broken->contact_id = $contact->id;
                    $broken->status = 1;
                    $broken->save();

                } catch (\Throwable) {

                    $broken->error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
                    $broken->save();

                    throw new \Exception($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                }
            }
        }
    }
}
