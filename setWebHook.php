<?php

require_once 'vendor/autoload.php';

ini_set('log_errors', 'On');
ini_set('error_log', 'logs.txt');

$telegramBot = new \TelegramBot\TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new \GuzzleHttp\Client(['http_errors' => false]));
$telegramBot->setWebHook('https://test.linux-electronics.ru/eventHandler.php');
