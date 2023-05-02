<?php

namespace App\Exceptions;

use App\Services\Telegram;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {

            Log::error(__METHOD__, [$e->getFile().' : '.$e->getLine(), $e->getMessage()]);

            $msg = strlen($e->getMessage()) > 50 ? substr($e->getMessage(), 0, 50) : $e->getMessage();

            Telegram::send('*Ошибка в коде!* '."\n".'*Где:* '.$e->getFile().' : '.$e->getLine()."\n".'*Текст:* '.$msg, env('TG_CHAT_DEBUG'), env('TG_TOKEN_DEBUG'));
        });
    }
}
