<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\HubspotController;
use App\Http\Controllers\OneCController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SegmentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ToolsController;
use App\Http\Middleware\SiteCheckMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//site
Route::post('site', [SiteController::class, 'index'])->middleware(SiteCheckMiddleware::class);

Route::get('site/cron', [SiteController::class, 'cron']);

//Route::post('segment', [SegmentController::class, 'hook']);

//1c
Route::post('pays/hook', [OneCController::class, 'pay']);

Route::post('pays/cron', [OneCController::class, 'cron']);

//tools
Route::post('tools/pay', [ToolsController::class, 'datePay']);

Route::post('tools/return', [ToolsController::class, 'return']);

Route::post('tools/sng', [ToolsController::class, 'sng']);

Route::post('tools/create', [ToolsController::class, 'createLead']);

Route::post('tools/country', [ToolsController::class, 'country']);

//tg
Route::get('telegram/proxy', [TelegramController::class, 'proxy']);

Route::post('telegram/create', [TelegramController::class, 'create']);

//sms
Route::post('sms/agreement', [SmsController::class, 'agreement']);

Route::post('sms/check', [SmsController::class, 'check']);

//sla
Route::post('sla/hook1', [SlaController::class, 'hook1']);

Route::post('sla/hook2', [SlaController::class, 'hook2']);

//course
Route::get('courses', [CourseController::class, 'get']);

Route::get('courses/update', [CourseController::class, 'update']);

//hb
Route::get('hubspot/get-broken', [HubspotController::class, 'getBroken']);

Route::get('hubspot/send-broken', [HubspotController::class, 'pushBroken']);

Route::get('hubspot/get-segment-python', [HubspotController::class, 'getSegmentPython']);

Route::get('hubspot/cron1', [HubspotController::class, 'cron1']);

Route::get('hubspot/cron2', [HubspotController::class, 'cron2']);

Route::get('hubspot/cron3', [HubspotController::class, 'cron3']);

Route::get('hubspot/cron4', [HubspotController::class, 'cron4']);

Route::get('hubspot/send', [HubspotController::class, 'send']);




