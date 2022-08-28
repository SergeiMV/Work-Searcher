<?php

namespace WorkSearcher\Controllers\Main;

use WorkSearcher\Controllers\Sites\WorkUaController;
use WorkSearcher\Controllers\Sites\RabotaUaController;
use WorkSearcher\Controllers\Senders\TelegramBotController;
use WorkSearcher\Models\User;
use WorkSearcher\Models\TelegramBot;
use GuzzleHttp\Client;
use Dotenv\Dotenv;

final class Main
{

    private array $users = [];
    private array $result = [];
    private array $parametersUrl;
    private string $getUpdatesUrl;
    private string $lastUpdateId = '';

    private TelegramBot $telegramBot;
    private TelegramBotController $telegramSender;
    private WorkUaController $workUa;
    private RabotaUaController $rabotaUa;
    private Client $client;
    private $data;


    public function initiate()
    {
        $dotenv = Dotenv::createImmutable(__DIR__, '../../../.env');
        $dotenv->load();

        $this->client = new Client();
        $this->telegramBot = new TelegramBot($_ENV['TELEGRAM_BOT_TOKEN']);
        $this->telegramSender = new TelegramBotController($this->telegramBot->getToken());
        $this->workUa = new WorkUaController();
        $this->rabotaUa = new RabotaUaController();
        $this->getUpdatesUrl = 'https://api.telegram.org/bot' . $this->telegramBot->getToken() . '/getUpdates?';

        $this->parametersUrl = [
            'offset' => $this->lastUpdateId,
        ];
    }


    private function getData()
    {
        $response = $this->client->request('POST', $this->getUpdatesUrl . http_build_query($this->parametersUrl));
        $this->data = json_decode($response->getBody()->getContents());
    }


    public function firstCheck()
    {
        $this->getData();
        if (isset($this->data->result[0]->update_id)) {
            $count = count($this->data->result);
            $this->parametersUrl['offset'] = $this->data->result[$count - 1]->update_id + 1;
        }
    }


    private function getRequests()
    {
        foreach ($this->data->result as $message) {
            $this->parametersUrl['offset'] = $message->update_id + 1;

            $userId = $message->message->from->id;
            $text = $message->message->text;

            if (!isset($this->users[$userId])) {
                $this->users[$userId] = new User($userId);
            }
            $this->telegramSender->checkRequest($this->users[$userId], $text);
        }
    }


    private function sendAnswers()
    {
        foreach ($this->users as $id => $user) {
            if (!empty($user->getErrorBag())) {
                $errors = $user->getErrorBag();
                $this->telegramSender->sendErrors($user, $errors);
                $user->deleteErrors();
            }
            if ($user->getSearchStatus()) {
                $this->workUa->parsingProcess($user);
                $subResult = $this->workUa->getNewJobs();
                $subResult ? $this->result[] = $subResult : null;

                $this->rabotaUa->parsingProcess($user);
                $subResult = $this->rabotaUa->getNewJobs();
                $subResult ? $this->result[] = $subResult : null;
                if ($this->result) {
                    $this->telegramSender->send($user, $this->result);
                    $this->result = [];
                }
            }
        }
    }


    public function run()
    {
        while (true) {
            $this->getData();
            $this->getRequests();
            $this->sendAnswers();
            sleep(1);
        }
    }
}
