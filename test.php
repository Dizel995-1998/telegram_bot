<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

use Curl\Curl;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$key = '010ef0062b53ab1e9b7ac112dca9f805';
$token = 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';

$trelloList = new \Trello\TrelloList();
$trelloList->createList('Моя новая колонка', '5fbe644ac20bdb66691ce587');
/*
$trelloCard->createCard('5fbe644ac20bdb66691ce589', 'Моя новая карточка', null);
$trelloCard->createCard('5fbe644ac20bdb66691ce589', 'Моя старая карточка', 'Тут моё описание');
*/

$trelloList = new \Trello\TrelloList();
$trelloList->setName('5fbe644ac20bdb66691ce589', 'Моя старая вселенная');

die();
$trelloList->setName('5fbe644ac20bdb66691ce589', 'Моя новая вселенная');



