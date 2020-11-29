<?php

require_once 'vendor/autoload.php';

use Curl\Curl;
use BugsManager\BugsManager;
use Logger\Logger;
use TelegramBot\TelegramBot;

ini_set('log_errors', 'On');
ini_set('error_log', 'logs.txt');

$telegramBot = new TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new Curl());

$currentChatID = $telegramBot->getChatId();

if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#fixed~') && strcmp($currentChatID, TELEGRAM_TEST_GROUP_CHAT_ID) === 0) {
    $message = $telegramBot->getReplyOriginText();
    $telegramBot->sendMessage(TELEGRAM_COMMON_GROUP_CHAT_ID, 'message', '-- Данный баг был исправлен --');
    $telegramBot->sendMessage(TELEGRAM_COMMON_GROUP_CHAT_ID, 'message', $message);
}

if ($telegramBot->messageHas('~^/help~')) {
    $telegramBot->sendMessage($currentChatID, 'message', TELEGRAM_COMMANDS_LIST);
}

if ($telegramBot->messageHas('~^/getInstructions~')) {
    $message = preg_replace('/\s+/', ' ', DESCRIPTION_HOW_WORK_BOT);
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

if ($telegramBot->messageHas('~^#баг~') && strcmp($currentChatID, TELEGRAM_COMMON_GROUP_CHAT_ID) === 0) {
    $message = str_replace('#баг', '', $telegramBot->getTextFromMessage());

    $message_for_testers = BugsManager::addNewBug($message) ?
        'Баг №' . BugsManager::getCountBug() . PHP_EOL .
        'Автор: ' . $telegramBot->getUserName() . PHP_EOL .
        'Описание: ' . $message . PHP_EOL
        :
        '[ERROR] Свяжитесь с разработчиками, не удалось записать баг в БД';

    $message_for_trello = $message_for_testers;
    if ($telegramBot->getMessageType() != 'message') {
        $message_for_trello .= 'Файл оставленный пользователем: ' . $telegramBot->getReferenceByFileID($telegramBot->getFileId());
    }

    $trelloCard = new \Trello\TrelloCard();
    $trelloCard->createCard('5fbe644ac20bdb66691ce589', 'Баг №' . BugsManager::getCountBug(), $message_for_trello);

    $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramBot->getMessageType(), $message_for_testers, $telegramBot->getFileId());
}