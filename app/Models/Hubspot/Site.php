<?php

namespace App\Models\Hubspot;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $table = 'hubspot_sites';

    protected $fillable = [
        'submitted_at',
        'firstname',
        'phone',
        'email',
        'connect_method',
        'persdata_consent',
        'coursename',
        'coursetype',
        'course_url',
        'courseid',
        'lead_id',
        'is_test',
        'contact_id',
        'status',
        'form',
        'body',
        'tg_nick',
        'clientid',
        'utm_source',
        'utm_medium',
        'utm_content',
        'utm_campaign',
        'utm_term',
        'type',
    ];

    public function prepareSend() : array
    {
        if ($this->form == 'a226fb88-ccbf-49ed-a617-8f7e4fc02412') {

            return [
                'product' => $this->coursename,
                'source'  => 'Форма Начать бесплатно',
                'type' => $this->coursetype,
                'tag'  => null,
                'url'  => $this->course_url,
                'course_id' => $this->courseid,
                'note' => 'Клиент заполнил форму начать бесплатно и получил материалы на почту. Связываемся и уточняем получил ли материал, почему интересуется и тд, предоставляем доп материалы, этому клиенту нужно чуть больше времени на принятии решения.
    Рассказываем подробно о школе , профессии и как проходит обучение.'
            ];
        }



        if ($this->form == '9be94507-6b84-441f-9648-9238fe26ff93') //попап режиссура

            return [
                'product' => 'Режиссер монтажа',
                'source'  => 'попап',
                'type' => 'Годовая программа',
                'tag'  => 'попап',
                'url'  => null,
                'course_id' => 439,
                'note' => 'Завка с Попапа с названием Хотите узнать как мы обучаем? Пришлем вам урок из курса'
            ];

        if ($this->form == 'c9115ddc-ea74-4428-a9f7-cf41f88fa93a') //попап интерьеры

            return [
                'product' => 'Дизайнер интерьеров',
                'source'  => 'попап',
                'type' => 'Годовая программа',
                'tag'  => 'попап',
                'url'  => null,
                'course_id' => 444,
                'note' => 'Завка с Попапа с названием Хотите узнать, как мы обучаем? Пришлем вам урок из курса!'
            ];

        if ($this->form == '8f64151d-f48a-40a7-a262-f2fc7ae27b8d') //попап старт в дизайне

            return [
                'product' => 'Дизайнер: старт в профессии',
                'source'  => 'попап',
                'type' => 'Годовая программа',
                'tag'  => 'попап',
                'url'  => null,
                'course_id' => 445,
                'note' => 'Завка с Попапа с названием Хотите узнать как мы обучаем? Пришлем вам урок из курса'
            ];

        if ($this->form == '6925f713-ef2f-4d81-a0fa-7b605ac605d1')

            return [
                'product' => 'Карьерный центр',
                'source'  => 'Карьерный центр',
                'type' => 'Курс',
                'tag'  => null,
                'url'  => null,
                'course_id' => null,
            ];

        if ($this->form == '0fa8c3bc-6411-4efb-b21f-a8598ae2096c')

            return [
                'product' => 'Тестировщик',
                'source'  => 'Попап',
                'type' => 'Годовая программа',
                'tag'  => 'шихман',
                'url'  => null,
                'course_id' => 456,
            ];

        if ($this->form == '77c0f622-3bb6-4412-b0b2-c46d424f7171' ||
            $this->form == 'eda7bd55-145f-4a27-92c3-e3b992397674' ||
            $this->form == '4c10b4fa-7fac-4c43-88e7-b074180b43dc' ||
            $this->form == '24da75d3-e794-47b7-bb45-06c81d8a9597' ||
            $this->form == 'e56687c0-1141-49c7-a706-2fa2941322c8' ||
            $this->form == '5a21b267-6531-44c3-a476-7cd4aae9260f' ||
            $this->form == '0af9faf3-c056-4023-8919-e654a384e21f')

            return [
                'product' => $this->coursename,
                'source'  => 'Лендинг Казахстан',
                'type' => $this->coursetype,
                'tag'  => null,
                'url'  => $this->course_url,
                'course_id' => $this->courseid,
            ];

        if ($this->form == 'fabc184d-eff7-4d4e-b370-63ce9f5f1b99' ||
            $this->form == 'd63fd44c-af4a-4a0e-bb35-63eb79a542ef' ||
            $this->form == 'd08571c8-0c65-473e-ace7-597ad70321f8')

            return [
                'product' => $this->coursename,
                'source'  => 'Лендинг вебфлоу',
                'type' => 'Годовая программа',
                'tag'  => null,
                'url'  => $this->course_url,
                'course_id' => $this->courseid,
            ];

        if ($this->form == '7f14eef7-c46b-4051-ad1d-557ce3d280e4')

            return [
                'product' => 'Помогите с выбором',
                'source'  => 'Лендинг вебфлоу',
                'type' => 'Годовая программа',
                'tag'  => null,
                'url'  => 'https://bangbangeducation.ru/sale',
                'course_id' => $this->courseid,
            ];

        if ($this->form == '9be94507-6b84-441f-9648-9238fe26ff93')

            return [
                'product' => null,//'Режиссура монтажа',
                'source'  => 'попап',
                'type' => 'Годовая программа',
                'tag'  => 'попап_урок_из курса',
                'url'  => null,//'https://bangbangeducation.ru/program/cinema-editing»',
                'course_id' => null,//439,
            ];

        if ($this->form == 'c8970cb9-5052-437e-939b-94f388a92312')

            return [
                'product' => null,//'Режиссура монтажа',
                'source'  => 'Брошенные корзины',
                'type' => null,
                'tag'  => 'БрошеннаяКорзина',
                'url'  => null,//'https://bangbangeducation.ru/program/cinema-editing»',
                'course_id' => null,//439,
            ];

        return [
            'product' => $this->coursename,
            'source'  => null,
            'type' => $this->coursetype,
            'tag'  => null,
            'url'  => $this->course_url,
            'course_id' => $this->courseid,
        ];
    }

    public function isDouble()
    {
        if ($this->is_test == 1)

            return false;

        return Site::query()
            ->where('id', '!=', $this->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s'))
            ->where('lead_id', '!=', null)
            ->where('email', $this->email)
            ->exists();
    }
}
