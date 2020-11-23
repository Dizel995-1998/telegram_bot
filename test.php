<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

$telegramBot = new \TelegramBot\TelegramBot(TOKEN, file_get_contents('php://input'), new \Curl\Curl());

$telegramBot->sendMessage(COMMON_GROUP_CHAT_ID, 'Hello World');