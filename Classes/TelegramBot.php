<?php

namespace Core;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TelegramBot
{
    private array $allowFileType = ["document", "video", "audio", "photo", "message"];
    private string $token;
    private string $telegramUrl = 'https://api.telegram.org/';
    private string $textMessage;
    private string $messageID;
    private string $fileId;
    private string $chatId;
    private string $userName;
    private string $messageType = "message"; // избавиться от инициализации
    private bool $replyMessageFlag = false;
    private string $replyMessageText;
    private string $replyMessageID;
    private int $errorCode;
    private string $errorDescription;
    private string $mediaGroupID;
    private Client $httpService;

    public function __construct($token, $incomingData, Client $httpService)
    {
        $this->token = $token;
        $this->httpService = $httpService;

        $incomingData = json_decode($incomingData, JSON_UNESCAPED_UNICODE);
        //Logger::writeData($incomingData, 2);
        //Logger::writeLine('------------------------------------------------');
        /* Получение данных из телеграмм сообщения */
        if (!empty($incomingData)) {
            $this->userName = isset($incomingData['message']['from']['first_name']) ?
                $incomingData['message']['from']['first_name'] : ' ';
            $this->chatId = isset($incomingData['message']['chat']['id']) ?
                (string) $incomingData['message']['chat']['id'] : " ";
            $this->textMessage = isset($incomingData['message']['text']) ?
                $incomingData['message']['text'] : " ";
            $this->textMessage = isset($incomingData['message']['caption']) ?
                $incomingData['message']['caption'] : $this->textMessage;
            $this->messageID = isset($incomingData['message']['message_id']) ?
                (string) $incomingData['message']['message_id'] : " ";
            $this->replyMessageFlag = isset($incomingData['message']['reply_to_message']);
            if ($this->replyMessageFlag) {
                $this->replyMessageID = isset($incomingData['message']['reply_to_message']['message_id']) ?
                   $incomingData['message']['reply_to_message']['message_id'] : " ";
                $this->replyMessageText = isset($incomingData['message']['reply_to_message']['text']) ?
                    (string) $incomingData['message']['reply_to_message']['text'] :
                    (string) $incomingData['message']['reply_to_message']['caption'];
            }

            foreach ($this->allowFileType as $type) {
                if (isset($incomingData["message"][$type])) {
                    $this->messageType = $type;
                    break;
                }
            }

            $this->mediaGroupID = isset($incomingData['message']['media_group_id']) ?
                $incomingData['message']['media_group_id'] : '0';

            if ($this->messageType == "photo") {
                $maxQualityPhotoIndex = count($incomingData["message"]["photo"])-1;
                $this->fileId = (string) $incomingData["message"]["photo"][(string) $maxQualityPhotoIndex]["file_id"];
            } else {
                $this->fileId = (string) $incomingData["message"][(string) $this->messageType]["file_id"];
            }
        }
    }


    public function getMediaGroupID()
    {
        return $this->mediaGroupID;
    }

    /**
     * @description Возвращает флаг яв-ся ли текущее сообщение ответом на другое сообщение
     * @return bool
     */
    public function replyMessage() : bool
    {
        return (bool) $this->replyMessageFlag;
    }

    /**
     * @description Возвращает текст сообщения на которое ссылается текущее сообщение
     * @return string
     */
    public function getReplyOriginText() : string
    {
        return (string) $this->replyMessageText;
    }

    /**
     * @description Возвращает ID сообщения на которое ссылается текущее сообщение
     * @return int
     */
    public function getReplyMessageID() : string
    {
        return (string) $this->replyMessageID;
    }


    public function sendMessage(string $chatID, string $messageType, string $messageText = null, string $replayToMessageID = null, string $fileID = null) : bool
    {
        if (!in_array($messageType, $this->allowFileType)) {
            throw new Exception('Dont supported this file type, see allow types');
        }
        $messageText = urlencode($messageText);
        $url = $this->telegramUrl . 'bot' . $this->token . '/send' . ucfirst($messageType) . '?chat_id=' . $chatID; // '&parse_mode=Markdown'; // . '&parse_mode=MarkdownV2'
        $url .= ($messageType != 'message' && isset($fileID)) ?
            '&' . $messageType . '=' . $fileID . '&caption=' . $messageText :
            '&text=' . $messageText;

        $url .= isset($replayToMessageID) ? '&reply_to_message_id=' . $replayToMessageID : '';

        $response = $this->httpService->post($url);
        $arResponse = json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
        $this->errorDescription = isset($arResponse['description']) ? $arResponse['description'] : " ";
        $this->errorCode = isset($arResponse['error_code']) ? $arResponse['error_code'] : 0;
        if ($this->errorCode != 0) Logger::writeLine('Не удалось отправить сообщение');
        return isset($arResponse['ok']);
    }

    /**
     * @return string
     */
    public function getMessageID() : string
    {
        return (string) $this->messageID;
    }

    /**
     * @description проверяет соотвествие текущего сообщения
     * с регулярным выражением, в случае соотвествия возвращает true
     * @param $pattern
     * @param false $matches
     * @return bool
     */
    public function messageHas($pattern, &$matches = false) : bool
    {
        return (bool) preg_match($pattern, $this->textMessage, $matches);
    }

    /**
     * Устанавливает WebHook на URL переданный в аргументах
     * @param $webHookURL
     * @throws GuzzleException
     */
    public function setWebHook($webHookURL)
    {
        $response = $this->httpService->request('GET',
            $this->telegramUrl . 'bot' . TELEGRAM_BOT_TOKEN . '/setWebHook?url='. $webHookURL);
        return json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getChatId() : string
    {
        return (string) $this->chatId;
    }

    /**
     * @return string
     */
    public function getFileId() : string
    {
        return (string) $this->fileId;
    }

    /**
     * @description Возвращает тип сообщения: документ, сообщение, аудио, видео
     * @return string
     */
    public function getMessageType(): string
    {
        return (string) $this->messageType;
    }

    /**
     * @description Возвращает текст сообщения
     * @return string
     */
    public function getTextFromMessage() : string
    {
        return (string) empty($this->textMessage) ? " " : $this->textMessage;
    }

    /**
     * @description Возвращает описание ошибки, если ошибки нет вернёт пустую строку
     * @return string
     */
    public function getErrorDescription() : string
    {
        return (string) empty($this->errorDescription) ? " " : $this->errorDescription;
    }

    /**
     * @description Возвращает код ошибки, если ошибки нет вернёт 0
     * @return int
     */
    public function getErrorCode() : int
    {
        return empty($this->errorCode) ? 0 : $this->errorCode ;
    }
    
    /**
     * @description Возвращает ник пользователя отправшего сообщение
     * @return string
     */
    public function getUserName(): string
    {
        return (string) $this->userName;
    }


    public function getReferenceByFileID(string $fileID) : string
    {
        $url = $this->telegramUrl . 'bot' . $this->token .'/getFile?file_id=' . $fileID;
        $response = $this->httpService->get($url);
        if ($response->getStatusCode() == 200) {
            $response = json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
            $result = $this->telegramUrl . 'file/bot' . $this->token . '/' . $response['result']['file_path'];
        } else {
            $result = "";
        }
        return $result;
    }


    public function answerOnMessageID(string $chatID, string $answerText, string $replyToMessageID) : bool
    {
        $url =
            $this->telegramUrl . 'bot' . $this->token . '/sendMessage?chat_id=' . $chatID .
            '&text=' . urlencode($answerText) . '&reply_to_message_id=' . $replyToMessageID;
        $response = $this->httpService->get($url);
        return $response->getStatusCode() == 200;
    }

    public function forwardMessage(string $targetChatID, string $fromChatID, string $messageID)
    {
        $url = $this->telegramUrl . 'bot' . $this->token . '/forwardMessage?chat_id=' . $targetChatID .
            '&from_chat_id=' . $fromChatID . '&message_id=' . $messageID;
        $this->httpService->get($url);
    }

    public function sendMediaPhoto(string $chatID, array $photosFileID, string $text)
    {
        $photo = '';

        for ($i = 0; $i < count($photosFileID); $i++) {
            $photo .= json_encode(['type' => 'photo', 'media' => $photosFileID[$i]]);
            if ($i < (count($photosFileID)-1)) {
                $photo .= ',';
            }
        }

        $url = $this->telegramUrl . 'bot' . $this->token . '/sendMediaGroup?chat_id=' . $chatID .
            '&caption=' . $text . '&media=[' . $photo . ']';
        $this->httpService->get($url);
        return $url;
    }
}