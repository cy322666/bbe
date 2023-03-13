<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'status',
        'lead_id',
        'contact_id',
        'sale',
        'count_leads',
        'error',
    ];
}
