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
$telegramMessageType = $telegramBot->getMessageType();

if ($currentChatID != TELEGRAM_TEST_GROUP_CHAT_ID) {
    if ($telegramBot->messageHas('~#(баг|bug) (?<text>.+)~', $matches)) {
        $message = $matches['text'];
        $telegramBot->sendMessage($currentChatID, 'message', 'Спасибо в содействии в улучшении сайта');
        if (BugsManager::addRowToBugs($message, $telegramAuthor, $telegramMessageID, $currentChatID, $telegramMediaGroupID)) {
            $lastBugID = BugsManager::getLastBugID();
            $message = prepareMessage($telegramAuthor, $lastBugID, $message);
            if ($telegramMessageType != 'message') {
                downloadImage($telegramBot->getReferenceByFileID($telegramFileID), $lastBugID, $telegramFileID);
            }
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramMessageType, $message, $telegramFileID);
            die();
        } else {
            // В любом случае пересылаем сообщение о баге тестировщикам и сообщаем им о проблеме
            Logger::writeLine('Не удалось записать баг в БД');
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramMessageType, $telegramMessageText, $telegramFileID);
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', 'Не удалось записать баг в БД, свяжитесь с разрабами');
        }
    }

    if ($telegramBot->messageHas('~#фича .+~')) {
        $telegramBot->sendMessage(TELEGRAM_FEATURES_CHAT_ID, $telegramMessageType, $telegramMessageText, $telegramFileID);
    }

    // Данный кусок кода запустится если сообщение было составным
    if ($telegramMediaGroupID) {
        $lastBugID = BugsManager::getLastBugID();
        $mediaGroupID = BugsManager::getAllInformationAboutBug($lastBugID)['message_group_id'];
        if ($mediaGroupID == $telegramMediaGroupID) {
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, $telegramMessageType, '', $telegramFileID);
            downloadImage($telegramBot->getReferenceByFileID($telegramFileID), $lastBugID, $telegramFileID);
        }
    }
} else { // ЕСЛИ МЫ В ГРУППЕ ТЕСТИРОВЩИКОВ
    if ($telegramBot->messageHas('~^/getCommands~')) {
        $telegramBot->sendMessage($currentChatID, 'message', TELEGRAM_COMMANDS_LIST);
    }

    if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#fixed~')) {
        if (preg_match('~Баг №(?<bug_id>\d+)~', $telegramBot->getReplyOriginText(), $matches)) {
            $arBug = BugsManager::getAllInformationAboutBug($matches['bug_id']);
            $telegramBot->answerOnMessageID($arBug['chat_id'], TELEGRAM_ANSWER_ON_FIX_BUG, $arBug['message_id']);
        } else {
            $telegramBot->sendMessage($currentChatID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
        }
    }

    if ($telegramBot->replyMessage() && $telegramBot->messageHas('~#trello (?<chooseTrelloBoard>#android|#ios|#web)~', $matchesBoard)) {
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

            $trelloBoard = '';
            $trelloColumn = '';

            switch ($matchesBoard['chooseTrelloBoard']) {
                case '#ios':
                    $trelloBoard = TRELLO_BOARD_FOR_ANDROID_IOS;
                    $trelloColumn = TRELLO_COLUMN_FOR_IOS;
                    break;

                case '#android':
                    $trelloBoard = TRELLO_BOARD_FOR_ANDROID_IOS;
                    $trelloColumn = TRELLO_COLUMN_FOR_ANDROID;
                    break;

                case '#web':
                    $trelloBoard = TRELLO_BOARD_FOR_WEB;
                    $trelloColumn = TRELLO_COLUMN_FOR_WEB;
                    break;
            }



            $cardStatusText = Facade::createCard(
                $trelloBoard, $trelloColumn, 'Баг №' . $bugID, $trelloMessage, 'top'
            ) ? 'Trello карта создана' : 'Не получилось создать Trello карту';

            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', $cardStatusText);
        } else {
            $telegramBot->sendMessage(TELEGRAM_TEST_GROUP_CHAT_ID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
        }
    }

    if ($telegramBot->messageHas('~^/fixBug (?<id>\d+)~', $matches)) {
        $message = BugsManager::bugFix($matches['id']) ?
            'Баг с ID ' . $matches['id'] . ' был помечен как исправленный' :
            '[ERROR] Сообщение о исправлении бага было отправлено пользователю, но не удалось изменить статус бага в БД';
        $arBug = BugsManager::getAllInformationAboutBug($matches['id']);
        $telegramBot->answerOnMessageID($arBug['chat_id'], 'Баг был помечен как исправленный, спасибо за содействие в улучшении сайта', $arBug['message_id']);
        $telegramBot->sendMessage($currentChatID, 'message', $message);
    }
}

if ($telegramBot->messageHas('~/getChatID~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'ChatID: ' . $currentChatID);
}

if ($telegramBot->messageHas('~/help~')) {
    $message = $currentChatID == TELEGRAM_TEST_GROUP_CHAT_ID ?
        DESCRIPTION_HOW_WORK_BOT_FOR_TESTERS : DESCRIPTION_HOW_WORK_BOT_USERS;

    $telegramBot->sendMessage($currentChatID, 'message', $message);
}
