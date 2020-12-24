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

$response = Facade::createCard('Мобильное приложение', 'Фидбэк Android', 'test', 'test', 'top');
var_dump($response);
die();
$body = ['cover' => ['color' => 'black'], 'name' => 'The Dark Knight'];
$httpClient = new Client(['http_errors' => false]);
$host = 'https://api.trello.com/1/cards/5fe331fe17139c5e59cb24d5?key=010ef0062b53ab1e9b7ac112dca9f805&token=af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953&cover=eyJjb2xvciI6InllbGxvdyJ9';
$response = $httpClient->put($host, ['json' => ($body)]);
var_dump(json_decode($response->getBody()->getContents(), true));
