<?php

namespace App\Http\Controllers;

use App\Jobs\ReturnLead;
use App\Models\Account;
use App\Models\Segment;
use App\Models\TgProxy;
use App\Services\amoCRM\Client;
use App\Services\Telegram;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;

class ToolsController extends Controller
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function datePay(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['status'][0]['id'] ?? $request->toArray()['leads']['add'][0]['id'];

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs()
            ->initCache();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($leadId);

        $lead->cf('Дата оплаты')->setDate(Carbon::now()->format('Y-m-d'));

        $product = $lead->cf('Тип продукта')->getValue();

        //автооплаты от админов
        if ($lead->responsible_user_id == 6103456 ||
            $lead->responsible_user_id == 5998951) exit;

        //ненужные в чате продукты
        if ($product !== 'Курс' &&
            $product !== 'Годовая программа' &&
            $lead->cf('Название продукта')->getValue() == 'Сайт (100%)' ||
            ($lead->cf('Название продукта')->getValue() == null ||
            $lead->cf('Тип продукта')->getValue() == null)) {

            exit;
        }

        $method = $lead->cf('Способ оплаты')->getValue();

        if ($method == 'Лерна') {

            $chatId = env('TG_CHAT_LERNA');
            $token  = env('TG_TOKEN_LERNA');
        } else {
            $chatId = env('TG_CHAT_CURATOR');
            $token  = env('TG_TOKEN_CURATOR');
        }

        $users = $amoApi->service->account->users;

        $responsibleName = '-';

        foreach ($users as $user) {

            if ($user->id == $lead->responsible_user_id) {

                $responsibleName = $user->name;
            }
        }

        $start = $lead->cf('Дата старта потока')->getValue() ? Carbon::parse($lead->cf('Дата старта потока')->getValue())->format('Y-m-d') : '-';

        $curator = ' ';

        $arrayMatch = [
            [
                'user'      => ' ',
                'dateStart' => '2023-10-30',
                'course'    => 'Графический дизайнер Plus',
            ],
            [
                'user'      => ' ',
                'dateStart' => '2023-10-30',
                'course'    => 'Графический дизайнер',
            ],
            [
                'user'      => ' ',
                'dateStart' => '2023-10-24',
                'course'    => 'UX/UI-дизайнер',
            ],
            [
                'user'      => '@janevetl',
                'dateStart' => '2023-10-30',
                'course'    => 'Иллюстратор',
            ],
            [
                'user'      => '@janevetl',
                'dateStart' => '2023-10-30',
                'course'    => 'Иллюстрация',
            ],
            [
                'user'      => ' ',
                'dateStart' => '2023-10-24',
                'course'    => 'UX/UI-дизайнер Plus',
            ],
            [
                'user'      => '@Garm_k',
                'dateStart' => '2023-10-16',
                'course'    => 'Моушн-дизайнер',
            ],
            [
                'user'      => '@nastyashalygina',
                'dateStart' => '2023-10-16',
                'course'    => 'Моушн-дизайнер Plus',
            ],
            [
                'user'      => '@protsenko_mur',
                'dateStart' => '2023-10-02',
                'course'    => 'Продакт-менеджер',
            ],
            [
                'user'      => '@vickylich',
                'dateStart' => '2023-10-16',
                'course'    => '2d-анимация',
            ],
            [
                'user'      => '@nbelows',
                'dateStart' => '2023-10-23',
                'course'    => '3D-художник',
            ],
            [
                'user'      => ' ',
                'dateStart' => '2023-10-30',
                'course'    => 'Иллюстратор',
            ],
            [
                'user'      => ' ',
                'dateStart' => '2023-11-10',
                'course'    => 'Режиссер монтажа',
            ],
            [
                'user'      => '@grewawewa',
                'dateStart' => '2023-10-01',
                'course'    => 'Основы Blender: создаем персонажа',
            ],
            [
                'user'      => '@grewawewa',
                'dateStart' => '2023-10-02',
                'course'    => 'Моушн-дизайн: от простого к сложному',
            ],
            [
                'user'      => ' ',
                'dateStart' => '2023-10-09',
                'course'    => 'Иллюстрация: ищем стиль',
            ],
            [
                'user'      => '@afflaty',
                'dateStart' => '2023-10-09',
                'course'    => 'Фотореалистичный рендер',
            ],
            [
                'user'      => '@anasyrova',
                'dateStart' => '2023-10-09',
                'course'    => 'Сторителлинг в иллюстрации',
            ],
            [
                'user'      => '@grewawewa',
                'dateStart' => '2023-10-11',
                'course'    => 'Текст: от статей до интерейса',
            ],
            [
                'user'      => '@grewawewa',
                'dateStart' => '2023-10-12',
                'course'    => 'Дизайн упаковки для реального мира',
            ],
            [
                'user'      => '@grewawewa',
                'dateStart' => '2023-10-16',
                'course'    => 'Айдентика: пошаговая инструкция',
            ],
            [
                'user'      => '@Garm_k',
                'dateStart' => '2023-10-24',
                'course'    => 'Дизайн мобильных приложений',
            ],

            [
                'user'      => ' ',
                'dateStart' => '2023-11-06',
                'course'    => 'Типографика: о форме и содержании',
            ],

            [
                'user'      => '@nabrodova',
                'dateStart' => '2023-10-30',
                'course'    => 'Графический дизайн',
            ],
            [
                'user'      => '@shiningmithra',
                'dateStart' => '2023-10-31',
                'course'    => 'Дизайн жилых интерьеров',
            ],
            [
                'user'      => '@shiningmithra',
                'dateStart' => '2023-10-31',
                'course'    => 'Режиссура монтажа',
            ],
            [
                'user'      => '@lisovix',
                'dateStart' => '2023-11-10',
                'course'    => 'Режиссура монтажа',
            ],
            [
                'user'      => '@AdelinaBarykina',
                'dateStart' => '2023-11-13',
                'course'    => '3D-художник',
            ],
            [
                'user'      => '@nastyashalygina',
                'dateStart' => '2023-11-20',
                'course'    => 'Моушн-дизайнер',
            ],
            [
                'user'      => '@grewawewa',
                'dateStart' => '2023-11-06',
                'course'    => 'Типографика: о форме и содержании',
            ],
            [
                'user'      => '@Garm_k',
                'dateStart' => '2023-11-13',
                'course'    => 'Смелый креатив: как легко придумывать прорывные идеи',
            ],
        ];

        foreach ($arrayMatch as $data) {

            if (strripos($lead->cf('Название продукта')->getValue(), $data['course']) !== false) {

                if ($start == $data['dateStart']) {

                    $curator = $data['user'];
                }
            }
        }

        Telegram::send(implode("\n", [
            '*Успешная сделка!* ',
            '-----------------------',
            '*Продукт*',
            'Название : '.$lead->cf('Название продукта')->getValue() ?? '-',
            'Тип : '.$lead->cf('Тип продукта')->getValue() ?? '-',
            'Дата старта потока : '.$start,
            'Ответственный : '.$responsibleName,
            'Гросс : '.$lead->sale,
            'Сумма nett : '.$lead->cf('Бюджет nett')->getValue() ?? '-',
            'Способ оплаты : '.$method,
            '*Клиент* ',
            'Имя : '.$lead->contact->name ?? '-',
            'Телеграм контакта : '.$lead->contact->cf('TelegramUsername_WZ')->getValue() ?? '-',
            'Телефон контакта : '.$lead->contact->cf('Телефон')->getValue() ?? '-',
            'Почта контакта : '.$lead->contact->cf('Email')->getValue() ?? '-',
            'Почта плательщика : '.$lead->cf('Почта плательщика')->getValue() ?? '-',
            'Почта студента : '.$lead->cf('Почта студента (оплата)')->getValue() ?? '-',
            "Куратор : $curator",
        ]), $chatId, $token, [
            "text" => "Перейти в сделку",
            "url"  => "https://bbeducation.amocrm.ru/leads/detail/".$leadId
        ], false
        );

        try {

            $lead->updated_at = time() + 10;
            $lead->save();

        } catch (\Throwable $e) {

            Log::error(__METHOD__, [$e->getMessage().' '.$e->getFile().' '.$e->getLine()]);
        }
    }

    /**
     * @throws Exception
     */
    public function country(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->leads['add'][0]['id'] ?? $request->leads['status'][0]['id'];

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        $lead = $amoApi->service->leads()->find($leadId);

        $contact = $lead->contact;

        if (!$contact->cf('Страна')->getValue()) {

            $dadata = new \Dadata\DadataClient(
                Env::get('DADATA_TOKEN'),
                Env::get('DADATA_SECRET'),
            );

            $response = $dadata->clean("phone", $contact->cf('Телефон')->getValue());

            Log::info(__METHOD__, [$response['country'] ?? 'Ошибка в отвте']);

            $contact->cf('Страна')->setValue($response['country']);
            $contact->save();
        }
    }

    public function return(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['add'][0]['id'];

        ReturnLead::dispatch($leadId);
    }

    /**
     * @throws Exception
     */
    public function createLead(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['add'][0]['id'] ?? null;// ?? $request->toArray()['status']['add'][0]['id'];

        if (!$leadId) exit;

        $segment = Segment::query()->create([
            'lead_id' => $leadId,
            'create_status' => 'push distribution',
        ]);

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        $leadBase = $amoApi
            ->service
            ->leads()
            ->find($leadId);

        $contact = $leadBase->contact;

        $leads = $contact->leads;

        $segment->count_leads = $leads->count();

        if ($leads->count() > 1) {

            $leadsActive = $leads->filter(function($lead) {

                if ($lead->status_id != 142 && $lead->status_id != 143) {

                    if ($lead->pipeline_id == 3342043 ||
                        $lead->pipeline_id == 6540894 ||
                        $lead->pipeline_id == 7206046) {

                        return $lead;
                    }
                }
            })->sortBy('pipeline_id', 'ASC');

            if ($leadsActive->count() > 1) {

                $segment->count_leads = $leadsActive->count();

                $leadBase->attachTag('В работе');

                foreach ($leadsActive as $leadActive) {

                    if ($leadActive->id != $leadBase->id) {

                        if (static::checkAdmin($leadActive->responsible_user_id)) continue;

                        $leadBase->responsible_user_id = $leadActive->responsible_user_id;
                        $leadBase->updated_at = time() + 5;
                        $leadBase->save();

                        $note = $leadBase->createNote(4);
                        $note->text = 'Сделка передана ответственному по активной сделке : '."\n".'https://bbeducation.amocrm.ru/leads/detail/'.$leadActive->id;
                        $note->element_type = 2;
                        $note->element_id = $leadActive->id;
                        $note->save();

                        $segment->responsible_user_id = $leadBase->responsible_user_id;
                        $segment->create_status = 'open lead';

                        break;
                    }
                }
            } else {
                //поиск задач
                foreach ($leads as $leadTask) {

                    if ($leadTask->closest_task_at > time()) {

                        if (static::checkAdmin($leadTask->responsible_user_id)) continue;

                        $leadBase->responsible_user_id = $leadTask->responsible_user_id;
                        $leadBase->updated_at = time() + 5;
                        $leadBase->save();

                        $note = $leadBase->createNote(4);
                        $note->text = 'Сделка передана ответственному по активной задаче в сделке : '."\n".'https://bbeducation.amocrm.ru/leads/detail/'.$leadTask->id;
                        $note->element_type = 2;
                        $note->element_id = $leadBase->id;
                        $note->save();

                        $segment->responsible_user_id = $leadBase->responsible_user_id;
                        $segment->create_status = 'open task';
                    }
                }
            }
        } else
            $segment->create_status = 'one lead';

        $segment->contact_id = $contact->id;
        $segment->save();

        $leadBase = $amoApi->service->leads()->find($segment->lead_id);

        if ($segment->responsible_user_id !== null &&
            $leadBase->responsible_user_id != $segment->responsible_user_id) {

            Log::warning(__METHOD__, [$leadBase->responsible_user_id.' != '.$segment->responsible_user_id]);

            if (static::checkAdmin($leadBase->responsible_user_id)) return;

            $leadBase->responsible_user_id = $segment->responsible_user_id;
            $leadBase->updated_at = time() + 5;
            $leadBase->save();

            $note = $leadBase->createNote(4);
            $note->text = 'Сделка передана ответственному повторно'."\n";
            $note->element_type = 2;
            $note->element_id = $leadBase->id;
            $note->save();

            $segment->create_status = 'repeat change responsible';
            $segment->save();
        }
    }

    /**
     * @throws \Exception
     */
    public function sng(Request $request)
    {
        Log::info(__METHOD__, $request->toArray());

        $leadId = $request->toArray()['leads']['add'][0]['id'] ?? $request->toArray()['leads']['status'][0]['id'];

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($leadId);

        $country = $lead->contact->cf('Страна')->getValue();

        if (in_array($country, [
            'Азербайджан',
            'Армения',
            'Беларусь',
            'Казахстан',
            'Кыргызстан',
            'Киргизстан',
            'Молдова',
            'Таджикистан',
            'Киргизия',
            'Узбекистан',
            'Белоруссия',
        ])) {

            Log::info(__METHOD__.' страна : '.$country);

            Log::info(__METHOD__.' отправлен СНГ');

            $lead->status_id = 60155626;
            $lead->save();
        }
    }

    private static function checkAdmin(int $responsible_user_id) : bool
    {
        $arrayAdmins = [
            5998951,
            6103456,
            6117505,

            //отпуск
//            9353222,
        ];

        return in_array($responsible_user_id, $arrayAdmins);
    }
}
