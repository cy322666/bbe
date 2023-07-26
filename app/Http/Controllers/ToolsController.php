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
            $lead->cf('Способ оплаты')->getValue() == 'Сайт (100%)' &&
            $lead->cf('Способ оплаты')->getValue() == 'Сайт (внутренняя рассрочка)') {

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

        $arrayMatch = [
            [
                'user'      => null,
                'dateStart' => '2023-06-19',
                'course'    => 'Иллюстрация',
            ],
            [
                'user'      => '@anasyrova',
                'dateStart' => '2023-06-13',
                'course'    => '2d-анимация',
            ],
            [
                'user'      => '@susan_sto_helit',
                'dateStart' => '2023-06-20',
                'course'    => 'UX/UI',
            ],
            [
                'user'      => '@nabrodova',
                'dateStart' => '2023-06-26',
                'course'    => 'Графический дизайн',
            ],
            [
                'user'      => '@afflaty',
                'dateStart' => '2023-06-05',
                'course'    => 'Моушн-дизайн ',
            ],
            [
                'user'      => '@dtxnv',
                'dateStart' => '2023-06-07',
                'course'    => 'Режиссура монтажа',
            ],
            [
                'user'      => '@shiningmithra',
                'dateStart' => '2023-06-19',
                'course'    => 'Дизайн жилых интерьеров',
            ],
            [
                'user'      => '@anasyrova',
                'dateStart' => '2023-06-20',
                'course'    => 'Иллюстрация: ищем стиль',
            ],
            [
                'user'      => '@vickylich',
                'dateStart' => '2023-05-31',
                'course'    => 'Иллюстрация: как приручить бумагу',
            ],
            [
                'user'      => '@vickylich',
                'dateStart' => '2023-06-26',
                'course'    => 'Дизайн персонажей',
            ],
            [
                'user'      => '@nbelows',
                'dateStart' => '2023-07-10',
                'course'    => '3D-художник',
            ],
            [
                'user'      => '@asyamarchenko',
                'dateStart' => null,
                'course'    => 'Айдентика: от идеи к визуальному воплощению',
            ],
            [
                'user'      => '@asyamarchenko',
                'dateStart' => null,
                'course'    => 'Типографика и верстка: все внимание к тексту',
            ],
            [
                'user'      => '@janevetl',
                'dateStart' => '2023-05-08',
                'course'    => 'Коллаж и фотореалистичный рисунок',
            ],
            [
                'user'      => '@afflaty',
                'dateStart' => '2023-06-05',
                'course'    => 'Кинетическая типографика',
            ],
            [
                'user'      => '@afflaty',
                'dateStart' => '2023-06-05',
                'course'    => 'Моушн-дизайн: от основ до реальных проектов',
            ],
            [
                'user'      => '@shiningmithra',
                'dateStart' => '2023-06-15',
                'course'    => 'Дизайн упаковки для реального мира',
            ],
            [
                'user'      => null,
                'dateStart' => '2023-06-19',
                'course'    => 'Векторная графика в Adobe Illustrator',
            ],
            [
                'user'      => null,
                'dateStart' => '2023-06-20',
                'course'    => 'Айдентика: пошаговая инструкция ',
            ],
            [
                'user'      => '@vickylich',
                'dateStart' => '2023-06-20',
                'course'    => 'Иллюстрация: ищем стиль',
            ],
            [
                'user'      => null,
                'dateStart' => '2023-06-26',
                'course'    => 'Моушн-дизайн: от простого к сложному',
            ],
            [
                'user'      => '@vickylich',
                'dateStart' => '2023-06-26',
                'course'    => 'Дизайн персонажей',
            ],
            [
                'user'      => null,
                'dateStart' => '2023-06-26',
                'course'    => 'Webflow: веб-дизайн без кода и границ',
            ],
            [
                'user'      => '@vickylich',
                'dateStart' => '2023-05-31',
                'course'    => 'Иллюстрация: как приручить бумагу',
            ],
            /*
            [
                'user'      => '@janevetl',
                'dateStart' => '2023-05-26',
                'course'    => 'Иллюстрация',
            ], [
                'user'      => '@anasyrova',
                'dateStart' => '2023-05-22',
                'course'    => '2D-анимация',
            ], */
            [
                'user'      => '@nabrodova',
                'dateStart' => '2023-07-04',
                'course'    => 'UX/UI',
            ],
            /*
            [
                'user'      => null,
                'dateStart' => '2023-05-22',
                'course'    => 'Графический дизайн',
            ],
            */
            [
                'user'      => '@afflaty',
                'dateStart' => '2023-06-05',
                'course'    => 'Моушн-дизайн',
            ],
            [
                'user'      => '@dtxnv',
                'dateStart' => '2023-06-07',
                'course'    => 'Режиссура монтажа',
            ], [
                'user'      => '@shiningmithra',
                'dateStart' => '2023-06-07',
                'course'    => 'Дизайн жилых интерьеров',
            ],
            /*[
                'user'      => '@anasyrova',
                'dateStart' => '2023-05-14',
                'course'    => 'Основы Blender',
            ], [
                'user'      => '@vickylich',
                'dateStart' => '2023-05-29',
                'course'    => 'Дизайн персонажей',
            ],
            [
                'user'      => '@garm_k',
                'dateStart' => '2023-05-15',
                'course'    => 'Арт-дирекшн цифрового продукта',
            ], [
                'user'      => '@garm_k',
                'dateStart' => '2023-05-19',
                'course'    => 'UX-исследования',
            ],
            [
                'user'      => '@afflaty',
                'dateStart' => '2023-05-22',
                'course'    => 'Моушн-дизайн: от простого к сложному',
            ], [
                'user'      => '@afflaty',
                'dateStart' => '2023-05-23',
                'course'    => 'Фотореалистичный рендер',
            ]
            */
        ];

        $curator = ' ';

        $start = $lead->cf('Дата старта потока')->getValue() ? Carbon::parse($lead->cf('Дата старта потока')->getValue())->format('Y-m-d') : '-';

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

        $leadId = $request->toArray()['leads']['add'][0]['id'];

        $segment = Segment::query()->create([
            'lead_id' => $leadId,
            'create_status' => 'push distribution',
        ]);

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initLogs()
            ->initCache();

        $lead = $amoApi
            ->service
            ->leads()
            ->find($leadId);

        $contact = $lead->contact;

        $leads = $contact->leads;

        if ($leads->count() > 1) {

            $leadsActive = $leads->filter(function($lead) {

                return
                    ($lead->status_id != 142 && $lead->status_id != 143) &&
                    ($lead->pipeline_id == 3342043 || $lead->pipeline_id == 6540894);

            })->sortBy('pipeline_id', 'ASC');

            if ($leadsActive->count() > 1) {

                $segment->count_leads = $leadsActive->count();

                $lead->attachTag('В работе');

                foreach ($leadsActive as $leadActive) {

                    if ($leadActive->id != $lead->id) {

                        $lead->responsible_user_id = $leadActive->responsible_user_id;
                        $lead->save();

                        $note = $lead->createNote(4);
                        $note->text = 'Сделка передана ответственному по активной сделке : '."\n".'https://bbeducation.amocrm.ru/leads/detail/'.$leadActive->id;
                        $note->element_type = 2;
                        $note->element_id = $leadActive->id;
                        $note->save();

                        $segment->responsible_user_id = $lead->responsible_user_id;
                        $segment->create_status = 'open lead';

                        break;
                    }
                }
            } else {
                //поиск задач
                foreach ($leads as $leadTask) {

                    if ($leadTask->closest_task_at > time()) {

                        $lead->responsible_user_id = $leadTask->responsible_user_id;
                        $lead->save();

                        $note = $lead->createNote(4);
                        $note->text = 'Сделка передана ответственному по активной задаче в сделке : '."\n".'https://bbeducation.amocrm.ru/leads/detail/'.$leadTask->id;
                        $note->element_type = 2;
                        $note->element_id = $lead->id;
                        $note->save();

                        $segment->responsible_user_id = $lead->responsible_user_id;
                        $segment->create_status = 'open task';
                    }
                }
            }
        } else
            $segment->create_status = 'one lead';

        $segment->contact_id = $contact->id;
        $segment->save();

        $lead = $amoApi->service->leads()->find($segment->lead_id);

        if ($lead->responsible_user_id != $segment->responsible_user_id) {

            Log::warning(__METHOD__, [$lead->responsible_user_id.' != '.$segment->responsible_user_id]);

            $lead->responsible_user_id = $segment->responsible_user_id;
            $lead->save();

            $segment->create_status = 'repeat change responsible';
            $segment->save();
        }
    }
}
