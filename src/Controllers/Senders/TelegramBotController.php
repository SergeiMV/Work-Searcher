<?php

namespace WorkSearcher\Controllers\Senders;

use GuzzleHttp\Client;
use WorkSearcher\Models\User;

class TelegramBotController
{
    private object $client;
    private string $mainUrl;
    private string $address = 'https://api.telegram.org/bot';
    private string $sendRequestUrl = '/sendMessage?chat_id=';

    public function __construct($botToken)
    {
        $this->client = new Client();
        $this->mainUrl = $this->address . $botToken . $this->sendRequestUrl;
    }

    public function send(User $user, $result)
    {
        $string = urlencode($this->arrayToString($result));
        $this->client->request('POST', $this->mainUrl . $user->getChatId() . '&text=' . $string);
    }

    private function arrayToString(array $array): string
    {
        $result = "";
        $count = 1;
        foreach ($array as $data) {
            foreach ($data as $link => $string) {
                if (gettype($string) === "array" || $link == 0) {
                    continue;
                }
                $result = $result . "№" . $count . "\nTitle:  " . $string . "\n" . 'Link:  ' . $link . "\n\n";
                $count++;
            }
        }
        return $result;
    }

    public function checkRequest(User &$user, string $text)
    {
        $text_array = explode(' ', $text);
        switch ($text_array[0]) {
            case '/begin':
                $user->setSearchStatus(true);
                break;
            case '/set_position':
                if (isset($text_array[1])) {
                    $string = '';
                    for ($count = 1; $count <= count($text_array) - 1; $count++) {
                        $string .= $text_array[$count];
                    }
                    $user->setPosition($string);
                } else {
                    $user->addError("Position is not set");
                }
                break;
            case '/set_parameters':
                if (isset($text_array[1])) {
                    for ($count = 1; $count <= count($text_array) - 1; $count++) {
                        $user->setParameters($text_array[$count]);
                    }
                } else {
                    $user->addError("No parameter is set");
                }
                break;
            case '/set_work_ua_loc':
                if (isset($text_array[1])) {
                    $user->setLocationWorkUa($text_array[1]);
                } else {
                    $user->addError("Work.ua location is not set");
                }
                break;
            case '/set_rabota_ua_loc':
                if (isset($text_array[1])) {
                    $user->setLocationRabotaUa($text_array[1]);
                } else {
                    $user->addError("Rabota.ua location is not set");
                }
                break;
            case '/stop':
                $user->setSearchStatus(false);
                break;
            case '/delete':
                $user->setPosition("");
                $user->setLocationWorkUa("");
                $user->setLocationRabotaUa("");
                $user->deleteAllParameters();
                break;
        }
    }

    public function sendErrors(User $user, array $errors)
    {
        $string = "";
        foreach ($errors as $error) {
            $string .= " " . $error . "\n";
        }
        $this->client->request('POST', $this->mainUrl . $user->getChatId() . '&text=' . $string);
    }
}
