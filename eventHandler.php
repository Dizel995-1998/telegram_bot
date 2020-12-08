<?php

use Core\BugsManager;
use Core\Logger;
use Core\TelegramBot;
use Core\Trello\Facade;
use GuzzleHttp\Client;

require_once 'vendor/autoload.php';
require_once 'someFunctions.php';

ini_set('log_errors', 'On');
ini_set('error_log', 'logs.txt');

$telegramBot = new TelegramBot(TELEGRAM_BOT_TOKEN, file_get_contents('php://input'), new Client(['http_errors' => false]));

$currentChatID = $telegramBot->getChatId();
$telegramMessageID = $telegramBot->getMessageID();
$telegramAuthor = $telegramBot->getUserName();
$telegramMessageText = $telegramBot->getTextFromMessage();
$telegramMediaGroupID = $telegramBot->getMediaGroupID();
$telegramFileID = $telegramBot->getFileId();

if ($currentChatID == TELEGRAM_COMMON_GROUP_CHAT_ID) {
    if ($telegramBot->messageHas('~#(баг|bug)~')) {
        $message = preg_replace('~#(баг|bug)~', '', $telegramMessageText);
        if (mb_strlen($message) > 2 && BugsManager::addRowToBugs($message, $telegramAuthor, $telegramMessageID, $telegramMediaGroupID)) {
            $lastBugID = BugsManager::getLastBugID();
            $message = prepareMessage($telegramAuthor, $lastBugID, $telegramMessageText);
            if ($telegramBot->getMessageType() != 'message') {
                saveBug($telegramBot->getReferenceByFileID($telegramFileID), $lastBugID, $telegramFileID);
            }
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramBot->getMessageType(), $message, $telegramBot->getFileId());
            die();
        } else {
            Logger::writeLine('Не удалось записать баг в БД');
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramBot->getMessageType(), $telegramMessageText, $telegramBot->getFileId());
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', 'Не удалось записать баг в БД, свяжитесь с разрабами');
        }
    }

    // Данный кусок кода запустится если сообщение было составным
    if ($telegramMediaGroupID) {
        $lastBugID = BugsManager::getLastBugID();
        $mediaGroupID = BugsManager::getAllInformationAboutBug($lastBugID)['message_group_id'];
        if ($mediaGroupID == $telegramMediaGroupID) {
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramBot->getMessageType(), '', $telegramFileID);
            saveBug($telegramBot->getReferenceByFileID($telegramFileID), $lastBugID, $telegramFileID);
        }
    }
}

if ($currentChatID == TELEGRAM_TEST_GROUP_CHAT_ID) {
    if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#fixed~')) {
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
            $trelloMessage = prepareMessage(
                    (string) $arInfoAboutBug['bug_author'],
                    (int) $bugID, (string) $arInfoAboutBug['bug_description']) .
                'Файлы прикреплённые к сообщению:' . PHP_EOL;

            foreach ($linksToFiles as $filePath) {
                $trelloMessage .= OUR_DOMAIN . $filePath . PHP_EOL;
            }

            $cardStatusText = Facade::createCard(
                TRELLO_BOARD_NAME, TRELLO_COLUMN_NAME, 'Баг №' . $bugID, $trelloMessage
            ) ? 'Trello карта создана' : 'Не получилось создать Trello карту';

            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', $cardStatusText);
        } else {
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
        }
    }

    if ($telegramBot->messageHas('~^/fixBug (?<id>\d+)~', $matches)) {
        $message = BugsManager::bugFix($matches['id']) ?
            'Баг с ID ' . $matches['id'] . ' был помечен как исправленный' :
            '[ERROR] не удалось изменить состояние бага на исправленный';
        $telegramBot->sendMessage($currentChatID, 'message', $message);
    }
}

if ($telegramBot->messageHas('~^/help~')) {
    $telegramBot->sendMessage($currentChatID, 'message', TELEGRAM_COMMANDS_LIST);
}

if ($telegramBot->messageHas('~^/getInstructions~')) {
    $message = preg_replace('/\s+/', ' ', DESCRIPTION_HOW_WORK_BOT);
    $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~^/getChatID~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'ChatID: ' . $currentChatID);
}
