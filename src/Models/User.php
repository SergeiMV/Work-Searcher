<?php

namespace WorkSearcher\Models;

use WorkSearcher\Data\Cities\RabotaUaCities;

class User
{

    private string $chatId = "";
    private string $position = "";
    private array $parameters = [];
    private string $locationWorkUa = "";
    private string $locationRabotaUa = "";
    private bool $searchStatus = false;
    private array $errorBag = [];

    public function __construct($id)
    {
        $this->chatId = $id;
    }

    public function getChatId()
    {
        return $this->chatId;
    }

    public function setPosition(string $data)
    {
        $this->position = $data;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setParameters(array | string $data)
    {
        $this->parameters[] = $data;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function deleteParameter(string $data)
    {
        if (in_array($data, $this->parameters)) {
            $key = array_search($data, $this->parameters);
            unset($this->parameters[$key]);
        }
    }

    public function deleteAllParameters()
    {
        $this->parameters = [];
    }

    public function setLocationWorkUa(string $location)
    {
        if (preg_match('/\w/', $location)) {
            $this->locationWorkUa = $location;
        } else {
            $this->errorBag[] =
                "Location for work.ua must contain of english names of cities, or 'remote' type location.";
        }
    }

    public function getLocationWorkUa()
    {
        return $this->locationWorkUa;
    }

    public function setLocationRabotaUa(string $location)
    {
        if (RabotaUaCities::getLocation($location) !== null) {
            $this->locationRabotaUa = $location;
        } else {
            $this->errorBag[] = "Location for rabota.ua must contain of east-slavik names of cities";
        }
    }

    public function getLocationRabotaUa()
    {
        return $this->locationRabotaUa;
    }

    public function setSearchStatus(bool $bool)
    {
        $this->searchStatus = $bool;
    }

    public function getSearchStatus()
    {
        return $this->searchStatus;
    }

    public function getErrorBag()
    {
        return $this->errorBag;
    }

    public function addError($error)
    {
        $this->errorBag[] = $error;
    }

    public function deleteErrors()
    {
        $this->errorBag = [];
    }
}
