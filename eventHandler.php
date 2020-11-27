<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

use Curl\Curl;
use BugsManager\BugsManager;
use Logger\Logger;
use TelegramBot\TelegramBot;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

ini_set('log_errors', 'On');
ini_set('error_log', 'logs.txt');

$telegramBot = new TelegramBot(TOKEN, file_get_contents('php://input'), new Curl());

$currentChatID = $telegramBot->getChatId();

if ($telegramBot->getMessageType() != "message") {
    $message = $telegramBot->getReferenceByFileID($telegramBot->getFileId());
    $telegramBot->sendMessage($currentChatID, "message", $message);
}

if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#fixed~') && strcmp($currentChatID, TEST_GROUP_CHAT_ID) === 0) {
    $message = $telegramBot->getReplyOriginText();
    $telegramBot->sendMessage(COMMON_GROUP_CHAT_ID, 'message', '-- Данный баг был исправлен --');
    $telegramBot->sendMessage(COMMON_GROUP_CHAT_ID, 'message', $message);
}

if ($telegramBot->messageHas('~^/help~')) {
    $telegramBot->sendMessage($currentChatID, 'message',
        '/getInstructions - получить инструкцию по использованию бота' . PHP_EOL .
        '/getChatID - вывести ID чата' . PHP_EOL .
        '#fixed - при ответе на сообщение с багом, отмечает баг как исправленный' . PHP_EOL .
        '/showCountAllBugs - счётчик общего количества багов' . PHP_EOL .
        '/showCountFixBugs - количество исправленных багов' . PHP_EOL .
        '/fixBug [id] - отметить баг с ID как исправленный' . PHP_EOL .
        '/getBug [id] - получить описание бага с ID' . PHP_EOL .
        '/getBugs [flag] - если flag = 1, возвращает список исправленных багов, иначе не исправленных' . PHP_EOL
    );
}

if ($telegramBot->messageHas('~^/getInstructions~')) {
    $message =
        'Есть два чата, для обычных пользователей и тестировщиков, все сообщения отмеченные
        в чате пользователей с хештэгом #баг будут отправлены к тестировщикам для последующей
        диагностики бага. Когда баг будет исправлен, нужно ответить на сообщение бота с описанием бага
        сообщением с хэштегом #fixed, сообщение будет передано пользователям с пометкой - исправленно';
    $message = preg_replace('/\s+/', ' ', $message);
    $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~^/getBugs (?<flag>\d)~', $matches)) {
    $arResult = [];
    $message = '';
    if ($matches['flag']) {
        $arResult = BugsManager::getAllBugs();
        $message = '-- Список не исправленных багов --' . PHP_EOL;
    } else {
        $arResult = BugsManager::getAllBugs(true);
        $message = '-- Список исправленных багов --' . PHP_EOL;
    }

    for ($i = 0; $i < count($arResult); $i++) {
        $message .=
            'Баг №' . $arResult[$i]['id'] . '' . PHP_EOL .
            'Описание: ' . $arResult[$i]['description_bug'];
    }
    if (!$telegramBot->sendMessage($currentChatID, 'message', $message)) {
        Logger::writeLine('Не удалось произвести отправку сообщения - ' . $message);
    }
}

if ($telegramBot->messageHas('~^/getBug (?<id>\d+)~', $matches)) {
    $message =
        'Баг №' . $matches['id'] . PHP_EOL . 'Описание: ' .
        BugsManager::getDescriptionByID($matches['id'])['description_bug'];
        $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~^/fixBug (?<id>\d+)~', $matches)) {

    $message = BugsManager::fixBugID($matches['id']) ?
        'Баг с ID ' . $matches['id'] . ' был помечен как исправленный' :
        '[ERROR] Внутренняя ошибка, обратитесь к разработкам';
    $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~^/showCountFixBugs~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'Количество исправленных багов - ' . BugsManager::getCountBug(true));
}

if ($telegramBot->messageHas('~^/showCountAllBugs~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'Общее количество багов - ' . BugsManager::getCountBug());
}

if ($telegramBot->messageHas('~^/getChatID~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'ChatID: ' . $telegramBot->getChatId());
}

if ($telegramBot->messageHas('~^#баг~') && strcmp($currentChatID, COMMON_GROUP_CHAT_ID) === 0) {
    $message = str_replace('#баг', '', $telegramBot->getTextFromMessage());

    $message_for_testers = BugsManager::addNewBug($message) ?
        'Баг №' . BugsManager::getCountBug() . PHP_EOL .
        'Автор: ' . $telegramBot->getUserName() . PHP_EOL .
        'Описание: ' . $message
        :
        '[ERROR] Свяжитесь с разработчиками, не удалось записать баг в БД';

    $telegramBot->sendMessage(TEST_GROUP_CHAT_ID, $telegramBot->getMessageType(), $message_for_testers, $telegramBot->getFileId());
}