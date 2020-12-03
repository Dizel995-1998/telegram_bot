<?php

require_once 'vendor/autoload.php';
require_once 'download.php';
require_once 'config/settings.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use TelegramBot\TelegramBot;
use GuzzleHttp\Client;
use BugsManager\BugsManager;

$telegramBot = new TelegramBot(TELEGRAM_BOT_TOKEN, null, new GuzzleHttp\Client(['http_errors' => false]));

