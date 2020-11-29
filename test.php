<?php

require_once 'vendor/autoload.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$curl = new Curl\Curl();
$curl->sendRequest('https://yandex.ru', 'GET');
var_dump($curl->getResponse());
die();
$trelloCard = new \Trello\TrelloCard();
//var_dump($trelloCard->getCard('aYlibKi4'));
$trelloCard->createCard('5fbe644ac20bdb66691ce589', 'Карточка 1', null);
$trelloCard->createCard('5fbe644ac20bdb66691ce589', 'Карточка 2', 'Тут моё описание');



var_dump($result['key'] ?? null);