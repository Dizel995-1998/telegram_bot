<?php

use Core\Trello\Board;
use Core\Trello\Card;
use Core\Trello\Facade;
use GuzzleHttp\Client;

require_once 'vendor/autoload.php';
require_once 'someFunctions.php';
require_once 'config/settings.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$myArray = ['первый элемент', 'второй элемент', 'третий', 'четвёртый', 'пятый'];

