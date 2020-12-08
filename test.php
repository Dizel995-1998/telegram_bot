<?php

require_once 'vendor/autoload.php';
require_once 'someFunctions.php';
require_once 'config/settings.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$telegramBot = new \Core\TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new \GuzzleHttp\Client(['http_errors' => false]));
$photoFilesID = [
    'AgACAgIAAxkBAAIF3V_OCeF0y-STt0jwswbLrVzW4uRQAAKUsDEbkzZwSpQzQQ8zbpar_h1Lli4AAwEAAwIAA20AA-RgBQABHgQ',
    'AgACAgIAAxkBAAIF3l_OCeFV48sWnlF6lFVBUURXCTqAAAKVsDEbkzZwSvkSPAABiGFlB_Mk6pcuAAMBAAMCAAN5AAOvBwQAAR4E'
];


$type = "photo";
$caption = "";
$media = [];

foreach ($photoFilesID as $file)
{
    $media[] = [
        'type' => $type,
        'media' => $file
    ];
}

$parameters = [
    'chat_id' => 'test_chat_id',
    'media' => json_encode($media)
];

var_dump($parameters);

die();
$url = $telegramBot->sendMediaPhoto('691046923', $photoFilesID, 'ss');
var_dump($url);