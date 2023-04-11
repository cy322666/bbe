<?php

namespace App\Services;

class Telegram
{
    public static function send(string $file, string $msg)
    {
        $getQuery = [
            "chat_id" 	 => '-979315059',
            "text"  	 => "*Ошибка в коде!* \n*Где:* $file \n*Текст:* $msg",
            "parse_mode" => "markdown"
        ];
        $ch = curl_init("https://api.telegram.org/bot". env('TG_TOKEN') ."/sendMessage?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }
}
