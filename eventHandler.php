<?php

require_once 'vendor/autoload.php';
require_once 'settings.php';

use BugsManager\BugsManager;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$common_group_chat_id =  "-1001484960835";
$testing_group_chat_id = "-1001470007699";

$telegramBot = new \TelegramBot\TelegramBot(TOKEN, file_get_contents('php://input'), new \Curl\Curl());

$localChatID = $telegramBot->getChatId();

if ($telegramBot->messageHas('/help')){
    $telegramBot->sendMessage($localChatID,
        '/getChatID - вывести ID чата%0A' .
        '/showCountAllBugs - счётчик общего количества багов%0A' .
        '/showCountFixBugs - количество исправленных багов%0A' .
        '/fixBug [id] - отметить баг с ID как исправленный%0A' .
        '/getDescriptionBugs - получить распечатку всех неисправленных багов%0A' .
        '/getDescriptionBug [id] - получить описание бага с ID%0A'
    );
}

if ($telegramBot->messageHas('/getDescriptionBugs')){
    $bugsManager = new BugsManager(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
    $arResult = $bugsManager->getAllBugs();

    foreach ($arResult as $item => $value){
        $telegramBot->sendMessage($localChatID,
            'Описание бага: ' . $arResult[$item]['description_bug'] . '%0A' .
            'ID бага - ' . $arResult[$item]['id']
            );
        usleep(500000);
    }
}

if ($telegramBot->messageHas('/getDescriptionBug')){
    $matches = 0;
    if (preg_match('~^/getDescriptionBug (?<id>\d+)~', $telegramBot->getTextFromMessage(), $matches)){
        $bugsManager = new BugsManager(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
        $telegramBot->sendMessage($localChatID, 'Описание бага с ID ' . $matches['id'] . '%0A'
        . $bugsManager->getDescriptionByID($matches['id'])["description_bug"]);

    } else {
        $telegramBot->sendMessage($localChatID, '[ERROR] Неверные входные данные');
    }
}

if ($telegramBot->messageHas('/fixBug')){
    $matches = 0;
    if (preg_match('~^/fixBug (?<id>\d+)~', $telegramBot->getTextFromMessage(), $matches)){
        $bugsManager = new BugsManager(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
        if ($bugsManager->fixBugID($matches['id']))
            $telegramBot->sendMessage($localChatID, 'Баг с ID = ' . $matches['id'] . ' был помечен как исправленный');
        else
            $telegramBot->sendMessage($localChatID, '[ERROR] Обратитесь к разработчикам');
    } else {
        $telegramBot->sendMessage($localChatID, '[ERROR] Неверные входные данные');
    }
}

if ($telegramBot->messageHas('/showCountFixBugs')){
    $bugsManager = new BugsManager(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
    $telegramBot->sendMessage($localChatID, 'Количество исправленных багов - ' . $bugsManager->getCountBug(true));
}

if ($telegramBot->messageHas('/showCountAllBugs')){
    $bugsManager = new BugsManager(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
    $telegramBot->sendMessage($localChatID, 'Общее количество багов - ' . $bugsManager->getCountBug());
}

if ($telegramBot->messageHas('/getChatID')){
    $telegramBot->sendMessage($localChatID, 'ChatID: ' . $telegramBot->getChatId());
}

if ($telegramBot->messageHas('#баг') && strcmp($localChatID, $common_group_chat_id) === 0){
    $bugsManager = new BugsManager(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);

    $bugsManager->addNewBug($telegramBot->getTextFromMessage());

    $message =
        'Пользователь - @' . $telegramBot->getUserName() . ', ' .
        'Номер бага №' . $bugsManager->getCountBug(). ' ,' .
        'Описание бага - ' . $telegramBot->getTextFromMessage();

    $message = str_replace('#баг', '', $message);

    if ($telegramBot->getMessageType() == 'message')
        $telegramBot->sendMessage($testing_group_chat_id, $message);
    else
        $telegramBot->sendFile($testing_group_chat_id, $telegramBot->getFileId(), $telegramBot->getMessageType(), $message);

    /*
    switch ($telegramBot->getMessageType())
    {
        case 'photo':
            $telegramBot->sendPhoto($testing_group_chat_id, $telegramBot->getFileId(), $message);
            break;

        case 'audio':
            $telegramBot->sendAudio($testing_group_chat_id, $telegramBot->getFileId(), $message);
            break;

        case 'document':
            $telegramBot->sendDocument($testing_group_chat_id, $telegramBot->getFileId(), $message);
            break;

        case 'video':
            $telegramBot->sendVideo($testing_group_chat_id, $telegramBot->getFileId(), $message);
            break;

        case 'message':
            $telegramBot->sendMessage($testing_group_chat_id, $message);
            break;
    } */
}
