<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

use BugsManager\BugsManager;

$telegramBot = new \TelegramBot\TelegramBot(TOKEN, file_get_contents('php://input'), new \Curl\Curl());

if ($telegramBot->messageHas('~^/help~'))
{
    $telegramBot->sendMessage($telegramBot->getChatId(),
        '/getChatID - вывести ID чата%0A' .
        '/showCountAllBugs - счётчик общего количества багов%0A' .
        '/showCountFixBugs - количество исправленных багов%0A' .
        '/fixBug [id] - отметить баг с ID как исправленный%0A' .
        '/getBug [id] - получить описание бага с ID%0A' .
        '/getBugs [flag] - если flag = 1, возвращает список исправленных багов, иначе не исправленных%0A'
    );
}

if ($telegramBot->messageHas('~^/getBugs (?<flag>\d)~', $matches))
{
    // Почему тут не нужно обьявление $arResult и $message
    if ($matches['flag']) {
        $arResult = BugsManager::getAllBugs();
        $message = '-- Список не исправленных багов --%0A';
    } else {
        $arResult = BugsManager::getAllBugs(true);
        $message = '-- Список исправленных багов --%0A';
    }

    for ($i = 0; $i < count($arResult); $i++)
    {
        $message .=
            'Описание бага: ' . $arResult[$i]['description_bug'] . '%0A' .
            'bug ID - ' . $arResult[$i]['id'] . '%0A';
    }
    $telegramBot->sendMessage($telegramBot->getChatId(), $message);
}

if ($telegramBot->messageHas('~^/getBug (?<id>\d+)~', $matches))
{
    $message = 'bug ID: ' . $matches['id'] . '%0A' .
    BugsManager::getDescriptionByID($matches['id'])['description_bug'];

    $telegramBot->sendMessage($telegramBot->getChatId(), $message);
}

if ($telegramBot->messageHas('~^/fixBug (?<id>\d+)~', $matches))
{
    $message = '';
    if (BugsManager::fixBugID($matches['id'])) {
        $message = 'Баг с ID ' . $matches['id'] . ' был помечен как исправленный';
    } else {
        $message = '[ERROR] Внутренняя ошибка, обратитесь к разработчикам';
    }

    $telegramBot->sendMessage($telegramBot->getChatId(), $message);
}

if ($telegramBot->messageHas('~^/showCountFixBugs~')){
    $telegramBot->sendMessage($telegramBot->getChatId(), 'Количество исправленных багов - ' . BugsManager::getCountBug(true));
}

if ($telegramBot->messageHas('~^/showCountAllBugs~')){
    $telegramBot->sendMessage($telegramBot->getChatId(), 'Общее количество багов - ' . BugsManager::getCountBug());
}

if ($telegramBot->messageHas('~^/getChatID~')){
    $telegramBot->sendMessage($telegramBot->getChatId(), 'ChatID: ' . $telegramBot->getChatId());
}

if ($telegramBot->messageHas('~^#баг~') && strcmp($telegramBot->getChatId(), COMMON_GROUP_CHAT_ID) === 0)
{
    $message = str_replace('#баг', '', $telegramBot->getTextFromMessage());

    if (BugsManager::addNewBug($message)) {
        $message_for_testers =
            'Пользователь - ' . $telegramBot->getUserName() . '%0A' .
            'заметил баг bugID: ' . BugsManager::getCountBug() . '%0A' .
            'Описание: ' . $message;

    } else {
        $message_for_testers = '[ERROR] Свяжитесь с разработчиками, не удалось записать баг в БД';
    }

    if ($telegramBot->getMessageType() == 'message') {
        $telegramBot->sendMessage(TEST_GROUP_CHAT_ID, $message_for_testers);
    } else {
        $telegramBot->sendFile(TEST_GROUP_CHAT_ID, $telegramBot->getFileId(), $telegramBot->getMessageType(), $message_for_testers);
    }
}