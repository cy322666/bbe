<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'hook_1',
        'hook_2',
        'time_minutes',
        'time_seconds',
    ];
}
