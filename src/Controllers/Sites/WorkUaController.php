<?php

namespace WorkSearcher\Controllers\Sites;

use WorkSearcher\Controllers\Senders\TelegramBot;
use WorkSearcher\Interfaces\SiteInterface;
use GuzzleHttp\Client;
use WorkSearcher\Models\User;

class WorkUaController implements SiteInterface
{
    private object $user;
    private string $location;
    private string $position;
    private array $parameters;
    private string $urlString = "";
    private array $newJobs;
    private array $parsingMatches;
    private array $lastJob = [];
    private string $searchUrl = 'https://work.ua/jobs';
    private string $pattern;

    public function __construct()
    {
        $this->pattern = '/<a href="(?<links>\/jobs\/.*?)" title="(?<titles>.*?' . date('Y') . ').*?<\/a>/';
    }

    public function validation(User $user)
    {
        if ($user->getLocationWorkUa()) {
            $this->location = $user->getLocationWorkUa();
            $this->urlString = $this->urlString . "-" . $this->location;
        }
        if ($user->getPosition()) {
            $this->position = $user->getPosition();
            $this->urlString = $this->urlString . "-" . $this->position;
        }
        if ($user->getParameters()) {
            foreach ($user->getParameters() as $parameter) {
                $this->parameters[] = $parameter;
                $this->urlString .= "+" . $parameter;
            }
        }
        $this->user = $user;
    }

    public function search()
    {
        $client = new Client();
        $response = $client->request('GET', $this->searchUrl . urlencode($this->urlString));
        preg_match_all($this->pattern, $response->getBody()->getContents(), $matches);
        $this->parsingMatches = $matches;
    }

    private function compare(array $jobs)
    {
        $result = [];
        for ($count = 0; $count < count($jobs['titles']); $count++) {
            if (
                isset($this->lastJob[$this->user->getChatId()]) &&
                $this->lastJob[$this->user->getChatId()] == $jobs['titles'][$count]
            ) {
                break;
            }
            $result["work.ua" . $jobs['links'][$count]] = html_entity_decode($jobs['titles'][$count]);
        }
        if (!empty($result)) {
            $this->lastJob[$this->user->getChatId()] = $jobs['titles'][0];
            $this->newJobs = $result;
        } else {
            $this->newJobs = [];
        }
    }

    public function getNewJobs()
    {
        $array = $this->newJobs;
        $this->newJobs = [];
        return $array;
    }

    public function parsingProcess(User $user)
    {
        $this->validation($user);
        $this->search();
        $this->compare($this->parsingMatches);
    }
}
