<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CourseController extends Controller
{
    public function update()
    {
        Artisan::call('courses:get');
    }
}
