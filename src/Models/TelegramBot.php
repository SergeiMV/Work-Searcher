<?php

namespace WorkSearcher\Models;

class TelegramBot
{

    private string $botToken;


    public function __construct(string $bot)
    {
        $this->botToken = $bot;
    }

    public function getToken()
    {
        return $this->botToken;
    }
}
