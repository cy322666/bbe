<?php

use App\Http\Controllers\OneCController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SegmentController;
use App\Http\Controllers\SiteController;
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

Route::post('site', [SiteController::class, 'index'])->middleware(SiteCheckMiddleware::class);

Route::post('segment', [SegmentController::class, 'hook']);

Route::post('pays/hook', [OneCController::class, 'pay']);

Route::post('pays/cron', [OneCController::class, 'cron']);

Route::post('tools/pay', [ToolsController::class, 'datePay']);

Route::post('tools/return', [ToolsController::class, 'return']);

Route::post('tools/sng', [ToolsController::class, 'sng']);

Route::post('tools/create', [ToolsController::class, 'createLead']);

Route::post('tools/country', [ToolsController::class, 'country']);

Route::post('products/list', [ProductController::class, 'list']);

Route::get('telegram/proxy', [TelegramController::class, 'proxy']);

Route::post('telegram/create', [TelegramController::class, 'create']);

Route::post('sms/agreement', [SmsController::class, 'agreement']);

Route::post('sms/check', [SmsController::class, 'check']);

