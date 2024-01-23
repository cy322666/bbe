<?php

namespace App\Models\Hubspot;

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
    ];

    public function prepareSend() : array
    {
        if ($this->form == '6925f713-ef2f-4d81-a0fa-7b605ac605d1')

            return [
                'product' => 'Карьерный центр',
                'source'  => 'Карьерный центр',
                'type' => 'Курс',
                'tag'  => null,
                'url'  => null,
                'course_id' => null,
            ];

        if ($this->form == '77c0f622-3bb6-4412-b0b2-c46d424f7171' ||
            $this->form == 'eda7bd55-145f-4a27-92c3-e3b992397674' ||
            $this->form == '4c10b4fa-7fac-4c43-88e7-b074180b43dc' ||
            $this->form == '24da75d3-e794-47b7-bb45-06c81d8a9597' ||
            $this->form == 'e56687c0-1141-49c7-a706-2fa2941322c8' ||
            $this->form == '0af9faf3-c056-4023-8919-e654a384e21f')

            return [
                'product' => $this->coursename,
                'source'  => 'Лендинг Казахстан',
                'type' => $this->coursetype,
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

        if ($this->form == '5a21b267-6531-44c3-a476-7cd4aae9260f')

            return [
                'product' => $this->coursename,
                'source'  => 'Лендинг Казахстан',
                'type' => 'Годовая программа',
                'tag'  => null,
                'url'  => $this->course_url,
                'course_id' => $this->courseid,
            ];

        if ($this->form == '9be94507-6b84-441f-9648-9238fe26ff93')

            return [
                'product' => 'Режиссура монтажа',
                'source'  => 'попап',
                'type' => 'Годовая программа',
                'tag'  => 'попап_урок_из курса',
                'url'  => 'https://bangbangeducation.ru/program/cinema-editing»',
                'course_id' => 439,
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
}
