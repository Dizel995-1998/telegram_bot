<?php

require_once 'vendor/autoload.php';

$telegramBot = new \TelegramBot\TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new Curl\Curl());
echo $telegramBot->setWebHook('https://test.linux-electronics.ru/eventHandler.php');
