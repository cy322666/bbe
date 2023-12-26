<?php

namespace App\Models\Hubspot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broken extends Model
{
    use HasFactory;

    protected $table = 'hubspot_brokens';

    protected $fillable = [
        'body',
        'submitted_at',
        'firstname',
        'phone',
        'email',
        'coursename',
        'coursetype',
        'course_url',
        'cart_status',
        'courseid',
        'lead_id',
        'contact_id',
        'status',
        'is_double',
        'is_test',
        'form',
    ];
}
