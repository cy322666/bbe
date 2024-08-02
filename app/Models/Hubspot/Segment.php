<?php

namespace App\Models\Hubspot;

use Carbon\Carbon;
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

    public function isDouble()
    {
        if ($this->is_test == 1)

            return false;

        return Segment::query()
            ->where('id', '!=', $this->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s'))
            ->where('lead_id', '!=', null)
            ->where('email', $this->email)
            ->exists();
    }
}
