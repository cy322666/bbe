<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Telegram
{
    /**
     * @throws GuzzleException
     */
    public static function send(string $file, string $msg)
    {
        if (strlen($msg) >= 4095) {

            $msg = substr($msg, 0, 50);

            (new Client())->get('https://api.telegram.org/bot' . env('TG_TOKEN') . '/sendMessage', [
                'query' => [
                    "chat_id" => '-979315059',
                    "text" => "*Ошибка в коде!* \n*Где:* $file \n*Текст:* $msg",
                    "parse_mode" => "markdown",
//                'reply_markup' => json_encode(['inline_keyboard' => [[$keyboard]]]),
                ]
            ]);
        }
    }
}
