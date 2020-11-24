<?php

namespace TelegramBot;

use Curl\Curl;
use Exception;

class TelegramBot
{
    private array $allowFileType = ['document', 'video', 'audio', 'photo', 'message'];
    private string $token;
    private string $telegramUrl = 'https://api.telegram.org/bot';
    private $textMessage;
    private $messageID;
    private $fileId;
    private $chatId;
    private $userName;
    private $messageType = 'message';
    private bool $replyMessageFlag = false;
    private $replyMessageText;
    private $replyMessageID;
    private $errorCode;
    private $errorDescription;
    private Curl $curl;

    public function __construct($token, $incomingData, Curl $curl)
    {
        $this->token = $token;
        $this->telegramUrl .= $this->token;
        $this->curl = $curl;

        // ДЛЯ ТЕСТА
        $file = fopen('telegram_answer.txt', 'a+');
        fwrite($file, $incomingData);

        $incomingData = json_decode($incomingData, JSON_UNESCAPED_UNICODE);
        /* Получение данных из телеграмм сообщения */
        if (count($incomingData) > 0) {
            $this->userName = (isset($incomingData['message']['from']['first_name'])) ?
                $incomingData['message']['from']['first_name'] : '';
            $this->chatId = (isset($incomingData['message']['chat']['id'])) ?
                $incomingData['message']['chat']['id'] : '';
            $this->textMessage = (isset($incomingData['message']['text'])) ?
                $incomingData['message']['text'] :
                $incomingData['message']['caption'];
            $this->messageID = isset($incomingData['message']['message_id']) ?
                $incomingData['message']['message_id'] : '';

            $this->replyMessageFlag = isset($incomingData['message']['reply_to_message']);

            if ($this->replyMessageFlag) {
                $this->replyMessageID = $incomingData['message']['reply_to_message']['message_id'];
                $this->replyMessageText = $incomingData['message']['reply_to_message']['text'];
            }

            foreach ($this->allowFileType as $type) {
                if (isset($incomingData['message'][$type])) {
                    $this->messageType = $type;
                    break;
                }
            }
            $this->fileId = ($this->messageType == 'photo') ?
                $incomingData['message'][$this->messageType][0]['file_id'] :
                $incomingData['message'][$this->messageType]['file_id'];
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
     * @return mixed
     */
    public function getReplyOriginText()
    {
        return $this->replyMessageText;
    }

    /**
     * @description Возвращает ID сообщения на которое ссылается текущее сообщение
     * @return mixed
     */
    public function getReplyMessageID()
    {
        return $this->replyMessageID;
    }

    /**
     * @description This method can send everyone supported file types of this class
     * @param $chatID
     * @param $fileType
     * @param null $messageText
     * @param null $fileID
     * @return bool
     * @throws Exception
     */
    public function sendMessage($chatID, $fileType, $messageText = null, $fileID = null) : bool
    {
        if (!in_array($fileType, $this->allowFileType)) {
            throw new Exception('Dont supported this file type, see allow types');
        }
        $url = $this->telegramUrl . '/send' . ucfirst($this->messageType) . '?chat_id=' . $chatID;
        $url .= ($fileType == 'message') ? '&text=' . $messageText : '&caption=' . $messageText;

        if ($fileType != 'message' && isset($fileID)) {
            $this->curl->setHeaders(['Content-Type: multipart/form-data']);
            $this->curl->withBody([$fileType => $fileID]);
        }
        $this->curl->sendRequest($url, 'POST');
        $arResponse = json_decode($this->curl->getResponse(), JSON_UNESCAPED_UNICODE);
        $this->errorDescription = $arResponse['description'];
        $this->errorCode = $arResponse['error_code'];
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
     * @deprecated
     * @param $chatId
     * @param $message
     * @throws Exception
     */
    public function sendMessageD($chatId, $message)
    {
        $this->curl->sendRequest(
            $this->telegramUrl . '/sendMessage?chat_id=' . $chatId . '&text=' . $message
        );
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
    public function getTextFromMessage(): string
    {
        return $this->textMessage;
    }

    /**
     * @description Возвращает описание ошибки, если она есть
     * @return false
     */
    public function getErrorDescription()
    {
        return empty($this->errorDescription) ? false : $this->errorDescription;
    }

    /**
     * @description Возвращает код ошибки, если она есть
     * @return false
     */
    public function getErrorCode()
    {
        return empty($this->errorCode) ? false : $this->errorCode ;
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
     * @deprecated
     * @param $chatID - ID чата в которые необходимо отправить сообщение
     * @param $fileID - FileID который нужно прикрепить к сообщению
     * @param $fileType - тип файла для отправки
     * @param false $captionText - текст сообщения
     * @throws Exception
     */
    public function sendFile($chatID, $fileID, $fileType, $captionText = false) : void
    {
        if (!in_array($fileType, $this->allowFileType)) {
            throw new Exception('Dont supported this file type');
        }
        $url = $this->telegramUrl . '/send' . ucfirst($this->messageType) . '?chat_id=' . $chatID;

        $url .= $captionText ? '&caption=' . $captionText : '';

        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody([$fileType => $fileID]);
        $this->curl->sendRequest($url, 'POST');
    }
}