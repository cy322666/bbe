<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;

    protected $fillable = [
        'send_code',
        'get_code',
        'id_sms',
        'status',
        'info',
        'result',
        'error',
        'lead_id',
        'phone',
        'contact_id',
        'is_agreement',
    ];
}
