<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'title',
        'slug',
        'url',
        'type_code',
        'has_date',
        'opened_at',
        'is_new',
        'price',
        'enabled',
    ];
}
