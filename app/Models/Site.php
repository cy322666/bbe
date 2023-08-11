<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'course',
        'product',
        'action',
        'amount',
        'course_id',
        'body',
        'is_test',
        'status',
        'utm_term',
        'utm_source',
        'utm_medium',
        'utm_content',
        'utm_campaign',
        'error',
    ];
}
