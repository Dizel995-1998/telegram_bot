<?php

require_once 'vendor/autoload.php';
require_once 'config/settings.php';
require_once 'download.php';

use Trello\TrelloCard;
use BugsManager\BugsManager;
use Logger\Logger;
use TelegramBot\TelegramBot;
use GuzzleHttp\Client;

ini_set('log_errors', 'On');
ini_set('error_log', 'logs.txt');

$telegramBot = new TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new Client(['http_errors' => false]));

$currentChatID = $telegramBot->getChatId();
$messageID = $telegramBot->getMessageID();
$author = $telegramBot->getUserName();
$messageGroupID = $telegramBot->getMediaGroupID();
$message = $telegramBot->getTextFromMessage();

if ($telegramBot->messageHas('~^#(баг|bug)~') && $currentChatID == TELEGRAM_COMMON_GROUP_CHAT_ID) {
    $message = str_replace('#баг', '', $message);
    if (BugsManager::addRowToBugs($message, $author, $messageID, $messageGroupID)) {
        $lastBugID = BugsManager::getLastBugID();
        $messageForTesters =
            'Баг №' . $lastBugID . PHP_EOL .
            'Автор: ' . $author . PHP_EOL .
            'Описание: ' . $message;

        $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', $messageForTesters);
        //die();
    } else {
        Logger::writeLine('Не смог записать описание бага в БД');
        die();
    }
}

if ($messageGroupID && $currentChatID == TELEGRAM_COMMON_GROUP_CHAT_ID || $telegramBot->getMessageType() != 'message') {
    $lastBugID = BugsManager::getLastBugID();
    $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramBot->getMessageType(), '', $telegramBot->getFileId());
    $url = $telegramBot->getReferenceByFileID($telegramBot->getFileId());
    $extensionFile = getExtensionFileByURL($url);
    $fileName = time() . rand(0, 9) . '.' . $extensionFile;

    if (!downloadFile($url, DOWNLOAD_DIRECTORY, $fileName)) {
        Logger::writeLine('Не смог загрузить изображение с сервера телеграмма');
        die();
    }

    if (!BugsManager::addFileToBug($lastBugID, DOWNLOAD_DIRECTORY . $fileName, $telegramBot->getFileId())) {
        Logger::writeLine('Не удалось записать путь к файлам бага');
    }
}

if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#fixed~') && $currentChatID == TELEGRAM_TEST_GROUP_CHAT_ID) {
    if (preg_match('~Баг №(?<bug_id>\d+)~', $telegramBot->getReplyOriginText(), $matches)) {
        $arBug = BugsManager::getAllInformationAboutBug($matches['bug_id']);
        $messageID = $arBug['message_id'];
        $telegramBot->answerOnMessageID(TELEGRAM_COMMON_GROUP_CHAT_ID, TELEGRAM_ANSWER_ON_FIX_BUG, $messageID);
    } else {
        $telegramBot->sendMessage($currentChatID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
    }
}

if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#trello~')) {
    if (preg_match('~Баг №(?<bug_id>\d+)~', $telegramBot->getReplyOriginText(), $matches)) {
        $bugID = $matches['bug_id'];

        $arInfoAboutBug = BugsManager::getAllInformationAboutBug($bugID);
        $linksToFiles = BugsManager::getPathToFilesByBugID($bugID);

        $trelloMessage =
            'Баг №' . $bugID . PHP_EOL .
            'Автор: ' . $arInfoAboutBug['bug_author'] . PHP_EOL .
            'Описание: ' . $arInfoAboutBug['bug_description'] . PHP_EOL .
            'Файлы прикреплённые к сообщению:' . PHP_EOL;

        foreach ($linksToFiles as $filePath) {
            $trelloMessage .= OUR_DOMAIN . $filePath . PHP_EOL;
        }

        $message = TrelloCard::createCard('5fbe644ac20bdb66691ce589', 'Баг №' . $bugID, $trelloMessage) ?
            'Trello карта создана' : 'Не получилось создать Trello карту';

        $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', $message);
    } else {
        $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
    }
}

if ($telegramBot->messageHas('~^/help~')) {
    $telegramBot->sendMessage($currentChatID, 'message', TELEGRAM_COMMANDS_LIST);
}

if ($telegramBot->messageHas('~^/getInstructions~')) {
    $message = preg_replace('/\s+/', ' ', DESCRIPTION_HOW_WORK_BOT);
    $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~^/fixBug (?<id>\d+)~', $matches)) {
    $message = BugsManager::bugFix($matches['id']) ?
        'Баг с ID ' . $matches['id'] . ' был помечен как исправленный' :
        '[ERROR] не удалось изменить состояние бага на исправленный';
    $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~^/getChatID~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'ChatID: ' . $currentChatID);
}
