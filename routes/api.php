<?php

use App\Http\Controllers\OneCController;
use App\Http\Controllers\SegmentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ToolsController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('site', [SiteController::class, 'create']);//->middleware('site');

Route::post('segment', [SegmentController::class, 'hook']);

Route::post('pays/hook', [OneCController::class, 'pay']);

Route::post('tools/pay', [ToolsController::class, 'datePay']);

Route::post('tools/return', [ToolsController::class, 'return']);

Route::post('tools/create', [ToolsController::class, 'createLead']);

Route::get('telegram/proxy', [TelegramController::class, 'proxy']);

Route::post('telegram/create', [TelegramController::class, 'create']);

