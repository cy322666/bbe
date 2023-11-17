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
}
