<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

/*
$telegramBot = new \TelegramBot\TelegramBot(TOKEN, file_get_contents('php://input'), new \Curl\Curl());
$telegramBot->sendMessage(COMMON_GROUP_CHAT_ID, 'Hello World');
*/

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$message = 'hello world';
$second_message = "hello world";

if ($message == $second_message)
    echo '=';
else
    echo '!=';

//var_dump($arResult);