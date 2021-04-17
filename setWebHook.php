<?php

use Core\TelegramBot;

require_once 'vendor/autoload.php';

ini_set('log_errors', 'On');
ini_set('error_log', 'logs.txt');

$telegramBot = new TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new \GuzzleHttp\Client(['http_errors' => false]));
$result = $telegramBot->setWebHook('https://test.05.ru/tg/eventHandler.php');
var_dump($result);
