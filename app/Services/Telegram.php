<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Telegram
{
    /**
     * @throws GuzzleException
     */
    public static function send(string $msg, string $chatId, string $token, ?array $keyboard)
    {
        if (strlen($msg) >= 4095) {
            $msg = substr($msg, 0, 50);
        }

        (new Client())->get('https://api.telegram.org/bot' . $token . '/sendMessage', [
            'query' => [
                "chat_id" => $chatId,
                "text"    => $msg,
                "parse_mode"   => "markdown",
                'reply_markup' => json_encode(['inline_keyboard' => [[$keyboard]]]),
            ]
        ]);
    }
}
