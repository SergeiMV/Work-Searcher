<?php

require 'vendor/autoload.php';

use WorkSearcher\Controllers\Main\Main;

$main = new Main();
$main->initiate();
$main->firstCheck();
$main->run();
