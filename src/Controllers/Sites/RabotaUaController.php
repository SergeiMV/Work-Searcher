<?php

namespace WorkSearcher\Controllers\Sites;

use WorkSearcher\Controllers\Senders\TelegramBot;
use WorkSearcher\Interfaces\SiteInterface;
use WorkSearcher\Data\Cities\RabotaUaCities;
use GuzzleHttp\Client;
use WorkSearcher\Models\User;

class RabotaUaController implements SiteInterface
{
    private object $user;
    private string $location = "";
    private string $position = "";
    private string $parameters = "";
    private string $urlParameters = "";
    private array $newJobs;
    private array $parsingMatches;
    private array $lastJob = [];
    private string $searchUrl = 'https://api.rabota.ua/vacancy/search?';

    public function validation(User $user)
    {
        if ($user->getLocationRabotaUa()) {
            if (RabotaUaCities::getLocation($user->getLocationRabotaUa()) !== null) {
                $this->location = "cityId=" . RabotaUaCities::getLocation($user->getLocationRabotaUa()) . "&";
                $this->urlParameters .= $this->location;
            } else {
                $error = 'Wrong location';
            }
        }
        if ($user->getPosition()) {
            $this->position = "keyWords=" . urlencode($user->getPosition()) . "&";
            $this->urlParameters .= $this->position;
        }
        if ($user->getParameters()) {
            $this->parameters = "";
            foreach ($user->getParameters() as $parameter) {
                $this->parameters .= "additionalKeywords=" .  urlencode($parameter) . "&";
            }
            $this->urlParameters .= $this->parameters;
        }
        $this->user = $user;
    }

    public function search()
    {
        $client = new Client();
        $response = $client
            ->request('GET', $this->searchUrl . $this->location . $this->position . $this->parameters);
        $results = json_decode($response->getBody()->getContents());
        $fullData = $results->documents;
        $matches = [];
        foreach ($fullData as $jobData) {
            $matches["rabota.ua/ua/company" . $jobData->notebookId . "/vacancy" . $jobData->id] =
                html_entity_decode($jobData->name);
        }
        $this->parsingMatches = $matches;
    }

    private function compare(array $jobs)
    {
        $result = null;
        foreach ($jobs as $link => $job) {
            if (isset($this->lastJob[$this->user->getChatId()]) && ($job == $this->lastJob[$this->user->getChatId()])) {
                break;
            }
            $result[$link] = $job;
        }
        if ($result) {
            $this->lastJob[$this->user->getChatId()] = reset($jobs);
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
