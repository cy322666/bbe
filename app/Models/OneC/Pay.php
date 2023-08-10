<?php

namespace App\Models\OneC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    use HasFactory;

    protected $table = '1c_pays';

    protected $fillable = [
        'datetime',
        'order_id',
        'number',
        'date',
        'payment_type',
        'title',
        'email',
        'sum',
        'return',
        'status',
        'code',
        'lead_id',
        'contact_id',
        'check_id',
        'installment_number',
        'sum_gross',
        'action',
        'action_name',
    ];
}
