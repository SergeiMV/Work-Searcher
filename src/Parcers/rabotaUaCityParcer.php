<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();
for ($count = 0; $count < 1400; $count++) {
    $response = $client->request('GET', 'https://api.rabota.ua/vacancy/search?cityId=' . $count . '&keyWord=PHP');
    $result = $response->getBody()->getContents();
    $results = json_decode($result);
    $resultsa = $results->documents;
    $resultsb = reset($resultsa);
    $cities[$resultsb->cityId] = $resultsb->cityName;
    echo "Step" . $count . "\n";
    sleep(1);
}

var_dump($cities);
