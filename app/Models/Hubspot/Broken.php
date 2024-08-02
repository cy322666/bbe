<?php

namespace App\Models\Hubspot;

use Carbon\Carbon;
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

    public function isDouble()
    {
        if ($this->is_test == 1)

            return false;

        return Broken::query()
            ->where('id', '!=', $this->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s'))
            ->where('lead_id', '!=', null)
            ->where('email', $this->email)
            ->exists();
    }
}
