<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

$telegramBot = new \TelegramBot\TelegramBot(TOKEN, file_get_contents('php://input'), new Curl\Curl());
echo $telegramBot->setWebHook('https://test.linux-electronics.ru/eventHandler.php');
