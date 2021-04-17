<?php

use Core\BugsManager;
use Core\Logger;
use Core\TelegramBot;
use Core\Trello\Board;
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

if ($currentChatID != TELEGRAM_TEST_GROUP_CHAT_ID && $currentChatID != TELEGRAM_FEATURES_CHAT_ID) {
    if ($telegramBot->messageHas('~^#(?<comm>баг|bug|фича)\s*(?<text>.*)~iu', $matches)) {
        $flag_feature = null;
        $themeTopic = 'Баг';
        $chatRedirect = TELEGRAM_TEST_GROUP_CHAT_ID;
        $messageForUsers = 'Зафиксирован баг №';

        $matches['comm'] = mb_strtolower($matches['comm']);

        if ($matches['comm'] == 'фича') {
            $themeTopic = 'Фича';
            $chatRedirect = TELEGRAM_FEATURES_CHAT_ID;
            $flag_feature = true;
            $messageForUsers = 'Зафиксирована фича №';
        }

        if (empty($matches['text'])) {
            $telegramBot->sendMessage($currentChatID, 'message', 'Опишите баг или фичу после соответвующих хештегов #баг, #bug или #фича');
            die();
        }

        $descriptionOfBugOrFeature = $matches['text'];

        if (BugsManager::addRowToBugs($descriptionOfBugOrFeature, $telegramAuthor, $telegramMessageID, $currentChatID, $chatRedirect, $flag_feature, $telegramMediaGroupID)) {
            $lastBugID = BugsManager::getLastBugID();
            $telegramBot->sendMessage($currentChatID, 'message', $messageForUsers . $lastBugID, $telegramMessageID);
            $message = prepareMessage($themeTopic, $telegramAuthor, $lastBugID, $descriptionOfBugOrFeature, '');

            if ($telegramMessageType != 'message') {
                downloadImage($telegramBot->getReferenceByFileID($telegramFileID), $lastBugID, $telegramFileID);
            }

            $telegramBot->sendMessage($chatRedirect, $telegramMessageType, $message, null, $telegramFileID);
            die();
        } else {
            // В любом случае пересылаем сообщение о баге тестировщикам и сообщаем им о проблеме
            $telegramBot->sendMessage($chatRedirect, $telegramMessageType, $telegramMessageText, null, $telegramFileID);
            $telegramBot->sendMessage($chatRedirect, 'message', 'Не удалось записать сообщение в БД, свяжитесь с разрабами');
        }
    }

    // Данный кусок кода запустится если сообщение было составным
    if ($telegramMediaGroupID) {
        $lastBugID = BugsManager::getLastBugID();
        $arBug = BugsManager::getAllInformationAboutBug($lastBugID);
        if ($arBug['message_group_id'] == $telegramMediaGroupID) {
            $telegramBot->sendMessage($arBug['redirect_chat_id'], $telegramMessageType, '', null, $telegramFileID);
            downloadImage($telegramBot->getReferenceByFileID($telegramFileID), $lastBugID, $telegramFileID);
        }
    }
} else { /** Если мы в чате тестировщиков */
    if ($telegramBot->replyMessage() && $telegramBot->messageHas('~^#fixed\s*(?<text>.*)~', $matchesText)) {
        if (preg_match('~Баг №(?<bug_id>\d+)~', $telegramBot->getReplyOriginText(), $matches)) {
            $arBug = BugsManager::getAllInformationAboutBug($matches['bug_id']);
            $answerMessage = !empty($matchesText['text']) ?
                TELEGRAM_ANSWER_ON_FIX_BUG . PHP_EOL . 'Сообщение от тестировщиков:' . PHP_EOL . $matchesText['text'] :
                TELEGRAM_ANSWER_ON_FIX_BUG;

            $telegramBot->answerOnMessageID($arBug['chat_id'], $answerMessage, $arBug['message_id']);
        } else {
            $telegramBot->sendMessage($currentChatID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
        }
    }

    if ($telegramBot->replyMessage() && $telegramBot->messageHas('~(#trello|#трелло) (?<chooseTrelloBoard>#android|#ios|#web|#фича|#маркетплейс)\s*(?<text>.*)~ui', $matchesBoard)) {
        if (preg_match('~(?<theme>Баг|Фича) №(?<bug_id>\d+)~', $telegramBot->getReplyOriginText(), $matches)) {
            $themeTopic = $matches['theme'];
            $bugID = $matches['bug_id'];

            if (BugsManager::getFlagCreatedTrelloCard($bugID)) {
                $telegramBot->sendMessage($currentChatID, 'message', 'Данная карточка уже существует');
                die();
            }

            $arInfoAboutBug = BugsManager::getAllInformationAboutBug($bugID);
            $linksToFiles = BugsManager::getPathToFilesByBugID($bugID);
            $trelloMessage = prepareMessage(
                    $themeTopic,
                    (string) $arInfoAboutBug['bug_author'],
                    (int) $bugID,
                    (string) $arInfoAboutBug['bug_description'], '**');

            $trelloMessage .= !empty($linksToFiles) ? '**Файлы прикрепленные к сообщению:**' . PHP_EOL : '';

            foreach ($linksToFiles as $filePath) {
                $trelloMessage .= OUR_DOMAIN . $filePath . PHP_EOL;
            }

            $trelloMessage .= empty($matchesBoard['text']) ? '' : 'Сообщение от тестировщиков: ' . $matchesBoard['text'];
            $trelloBoard = '';
            $trelloColumn = '';
            $matchesBoard['chooseTrelloBoard'] = mb_strtolower($matchesBoard['chooseTrelloBoard']);

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

                case '#фича':
                    $trelloBoard = TRELLO_BOARD_FOR_WEB;
                    $trelloColumn = TRELLO_COLUMN_FOR_FEATURES;
                    break;

                case '#маркетплейс':
                    $trelloBoard = TRELLO_BOARD_FOR_MARKETPLACE;
                    $trelloColumn = TRELLO_COLUMN_FOR_MARKETPLACE;
                    break;
            }

            $cardName = $themeTopic . ' №' . $bugID . ' ' . (string) mb_substr($arInfoAboutBug['bug_description'], 0, 50);

            $cardStatusText = Facade::createCard(
                $trelloBoard, $trelloColumn, $cardName, $trelloMessage, 'top'
            ) ? 'Trello карта создана' : 'Не получилось создать Trello карту';

            Facade::addLabelOnCard($trelloBoard, $cardName, $themeTopic);

            if ($cardStatusText == 'Trello карта создана') {
                BugsManager::setFlagCreatedTrelloCard($bugID);
            }
            $telegramBot->sendMessage($currentChatID, 'message', $cardStatusText);
        } else {
            $telegramBot->sendMessage($currentChatID, 'message', 'Не удалось определелить ID бага, свяжитесь с разработчиками');
        }
    }

    if ($telegramBot->messageHas('~^/fixBug (?<id>\d+)\s*(?<text>.*)~', $matches)) {
        $arBug = BugsManager::getAllInformationAboutBug($matches['id']);

        if (empty($arBug)) {
            $telegramBot->sendMessage($currentChatID, 'message', 'Не удалось найти данный баг в БД');
            die();
        }

        if ($arBug['bug_fix']) {
            $telegramBot->sendMessage($currentChatID, 'message', 'Данный баг помечен как уже исправленный');
            die();
        }

        $message = BugsManager::bugFix($matches['id']) ?
            'Баг с ID ' . $matches['id'] . ' переведён в статус исправленных' :
            '[ERROR] Сообщение о исправлении бага было отправлено пользователю, но не удалось изменить статус бага в БД';

        $answerMessage = 'Данный баг был исправлен';
        $answerMessage .= empty($matches['text']) ? '' : PHP_EOL . '**Сообщение от тестировщиков:** ' . $matches['text'];

        $telegramBot->answerOnMessageID($arBug['chat_id'], $answerMessage, $arBug['message_id']);
        $telegramBot->sendMessage($currentChatID, 'message', $message);
    }
}

if ($telegramBot->messageHas('~/getChatID~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'ChatID: ' . $currentChatID);
}

if ($telegramBot->messageHas('~^/help~')) {
    $message = $currentChatID == TELEGRAM_TEST_GROUP_CHAT_ID || $currentChatID == TELEGRAM_FEATURES_CHAT_ID ?
        TELEGRAM_COMMANDS_LIST : DESCRIPTION_HOW_WORK_BOT_USERS;

    $telegramBot->sendMessage($currentChatID, 'message', $message);
}

if ($telegramBot->messageHas('~#getMessageID~')) {
    $telegramBot->sendMessage($currentChatID, 'message', 'MessageID: ' . $telegramMessageID);

    if ($telegramMessageType != 'message') {
        $telegramBot->sendMessage($currentChatID, 'message', 'fileID: ' . $telegramFileID);
    }
}