<?php 

require 'vendor/autoload.php';
use WorkSearcher\Controllers\Sites\WorkUaController;
use WorkSearcher\Controllers\Sites\RabotaUaController;
use WorkSearcher\Controllers\Senders\TelegramBotController;
use WorkSearcher\Models\User;
use WorkSearcher\Models\TelegramBot;
use GuzzleHttp\Client;

$users = [];

$telegramBot = new TelegramBot(''); //write here in round brackets your telegram bot token

$result = NULL;

$getUpdatesUrl = 'https://api.telegram.org/bot' . $telegramBot->getToken() . '/getUpdates?';
$lastUpdateId = null;
$parametersUrl = [
    "offset" => $lastUpdateId,
];


$client = new Client();
$telegramSender = new TelegramBotController($telegramBot->getToken());

$workUa = new WorkUaController();
$rabotaUa = new RabotaUaController();

while (true) {
    $response = $client->request('POST', $getUpdatesUrl . http_build_query($parametersUrl));
    $data = json_decode($response->getBody()->getContents());
    
    foreach ($data->result as $message) {
        $parametersUrl['offset'] = $message->update_id + 1;
        $userId = $message->message->from->id;
        $text = $message->message->text;
	
        if (!isset($users[$userId])) {
            $users[$userId] = new User($userId);
	}
	$telegramSender->checkRequest($users[$userId], $text);
    }

    foreach ($users as $id => $user) {
        if (!empty($user->getErrorBag())) {
            $errors = $user->getErrorBag();
	    $telegramSender->sendErrors($user, $errors);
	    $user->deleteErrors();
	}
	if($user->getSearchStatus()) {
            $workUa->parsingProcess($user);
            $subResult = $workUa->getNewJobs();
	    $subResult ? $result[] = $subResult : NULL;

            $rabotaUa->parsingProcess($user);
            $subResult = $rabotaUa->getNewJobs();
            $subResult ? $result[] = $subResult : NULL;
            if ($result) {
                $telegramSender->send($user, $result);
                $result = [];
            }
	}
    }
    sleep(1);
}
