<?php

namespace TelegramBot;

use Curl\Curl;
use Exception;
use Logger\Logger;

class TelegramBot
{
    private array $allowFileType = ["document", "video", "audio", "photo", "message"];
    private string $token;
    private string $telegramUrl = 'https://api.telegram.org/';
    private string $textMessage;
    private $messageID;
    private $fileId;
    private $chatId;
    private $userName;
    private string $messageType = "message"; // избавиться от инициализации
    private bool $replyMessageFlag = false;
    private string $replyMessageText;
    private int $replyMessageID;
    private int $errorCode;
    private string $errorDescription;
    private Curl $curl;

    public function __construct($token, $incomingData, Curl $curl)
    {
        $this->token = $token;
        $this->curl = $curl;

        Logger::writeLine($incomingData);

        $incomingData = json_decode($incomingData, JSON_UNESCAPED_UNICODE);
        /* Получение данных из телеграмм сообщения */
        if (!empty($incomingData)) {
            $this->userName = isset($incomingData['message']['from']['first_name']) ?
                $incomingData['message']['from']['first_name'] : ' ';
            $this->chatId = isset($incomingData['message']['chat']['id']) ?
                $incomingData['message']['chat']['id'] : ' ';
            $this->textMessage = isset($incomingData['message']['text']) ?
                $incomingData['message']['text'] : "";
            $this->textMessage = isset($incomingData['message']['caption']) ?
                $incomingData['message']['caption'] : $this->textMessage;
            $this->messageID = isset($incomingData['message']['message_id']) ?
                $incomingData['message']['message_id'] : ' ';
            $this->replyMessageFlag = isset($incomingData['message']['reply_to_message']);
            if ($this->replyMessageFlag) {
                $this->replyMessageID = isset($incomingData['message']['reply_to_message']['message_id']) ?
                    $incomingData['message']['reply_to_message']['message_id'] : 0;
                $this->replyMessageText = isset($incomingData['message']['reply_to_message']['text']) ?
                    $incomingData['message']['reply_to_message']['text'] :
                    $incomingData['message']['reply_to_message']['caption'];
            }

            foreach ($this->allowFileType as $type) {
                if (isset($incomingData["message"][$type])) {
                    $this->messageType = $type;
                    break;
                }
            }

            if ($this->messageType == "photo") {
                $maxQualityPhotoIndex = count($incomingData["message"]["photo"])-1;
                $this->fileId = $incomingData["message"]["photo"][$maxQualityPhotoIndex]["file_id"];
            } else {
                $this->fileId = $incomingData["message"][$this->messageType]["file_id"];
            }
        }
    }

    /**
     * @description Возвращает флаг яв-ся ли текущее сообщение ответом на другое сообщение
     * @return bool
     */
    public function replyMessage() : bool
    {
        return $this->replyMessageFlag;
    }

    /**
     * @description Возвращает текст сообщения на которое ссылается текущее сообщение
     * @return string
     */
    public function getReplyOriginText() : string
    {
        return $this->replyMessageText;
    }

    /**
     * @description Возвращает ID сообщения на которое ссылается текущее сообщение
     * @return int
     */
    public function getReplyMessageID() : int
    {
        return $this->replyMessageID;
    }

    /**
     * @description This method can send everyone supported file types of this class
     * @param $chatID
     * @param string $messageType
     * @param null $messageText
     * @param null $fileID
     * @return bool
     * @throws Exception
     */
    public function sendMessage($chatID, string $messageType, $messageText = null, $fileID = null) : bool
    {
        if (!in_array($messageType, $this->allowFileType)) {
            throw new Exception('Dont supported this file type, see allow types');
        }
        $messageText = urlencode($messageText); // Возможно нужно убрать

        $url = $this->telegramUrl . 'bot' . $this->token . '/send' . ucfirst($messageType) . '?chat_id=' . $chatID;
        $url .= ($messageType == 'message') ? '&text=' . $messageText : '&caption=' . $messageText;

        if ($messageType != 'message' && isset($fileID)) {
            $this->curl->setHeaders(['Content-Type: multipart/form-data']);
            $this->curl->withBody([$messageType => $fileID]);
        }
        $this->curl->sendRequest($url, 'POST');

        $arResponse = json_decode($this->curl->getResponse(), JSON_UNESCAPED_UNICODE);
        $this->errorDescription = isset($arResponse['description']) ? $arResponse['description'] : " ";
        $this->errorCode = isset($arResponse['error_code']) ? $arResponse['error_code'] : 0;
        return isset($arResponse['ok']) ? $arResponse['ok'] : false;
    }

    /**
     * @description возвращает ID текущего сообщения
     * @return mixed|string
     */
    public function getMessageID()
    {
        return $this->messageID;
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
     * @description Устанавливает URL на который будет возвращаться webhook
     * @param $webHookURL
     * @throws Exception
     */
    public function setWebHook($webHookURL) : void
    {
        $this->curl->sendRequest($this->telegramUrl . '/setWebHook?url=' . $webHookURL);
    }

    /**
     * @description Возвращает текущий ID чата
     * @return string
     */
    public function getChatId(): string
    {
        return $this->chatId;
    }

    /**
     * @description Возвращает ID файла приклеплённого к сообщению ( если такой есть )
     * @return mixed
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @description Возвращает тип сообщения: документ, сообщение, аудио, видео
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @description Возвращает текст сообщения
     * @return string
     */
    public function getTextFromMessage() : string
    {
        return empty($this->textMessage) ? " " : $this->textMessage;
    }

    /**
     * @description Возвращает описание ошибки, если ошибки нет вернёт пустую строку
     * @return string
     */
    public function getErrorDescription() : string
    {
        return empty($this->errorDescription) ? " " : $this->errorDescription;
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
        return $this->userName;
    }

    /**
     * @description Возвращает URL где хранится файл на серверах телеграмма по его fileID
     * @param $fileID
     * @return string
     * @throws Exception
     */
    public function getReferenceByFileID($fileID) : string
    {
        $url = $this->telegramUrl . 'bot' . $this->token .'/getFile?file_id=' . $fileID;
        $this->curl->sendRequest($url);
        $response = json_decode($this->curl->getResponse(), true);
        return ($response['ok'] == true) ?
            $this->telegramUrl . 'file/bot' . $this->token . '/' . $response['result']['file_path'] : " ";
    }

    /**
     * @description Отвечает на сообщение replyToMessageID текстом answerText в чате chatID,
     * возвращает статус успешности произведённой операции
     * @param string $chatID
     * @param string $answerText
     * @param string $replyToMessageID
     * @return bool
     * @throws Exception
     */
    public function answerOnMessageID(string $chatID, string $answerText, string $replyToMessageID) : bool
    {
        $url =
            $this->telegramUrl . 'bot' . $this->token . '/sendMessage?chat_id=' . $chatID .
            '&text=' . $answerText . '&reply_to_message_id=' . $replyToMessageID;
        $this->curl->sendRequest($url);
        return $this->curl->getResponseCode() == 200;
    }
}