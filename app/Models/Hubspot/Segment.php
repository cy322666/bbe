<?php

namespace App\Models\Hubspot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $table = 'hubspot_segments';

    protected $fillable = [
        'vid',
        'email',
        'phone',
        'firstname',
        'body',
        'is_test',
    ];
}
