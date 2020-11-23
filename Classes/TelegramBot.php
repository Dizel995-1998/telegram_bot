<?php

namespace TelegramBot;

use Curl\Curl;
use Exception;

class TelegramBot
{
    private array $allowFileType = ['document', 'video', 'audio', 'photo'];
    private string $token;
    private string $telegramUrl = 'https://api.telegram.org/bot';
    private string $textMessage;
    private string $fileId;
    private string $chatId;
    private string $userName;
    private string $messageType = 'message';
    private Curl $curl;

    public function __construct($token, $incomingData, Curl $curl)
    {
        $this->token = $token;
        $this->telegramUrl .= $this->token;
        $this->curl = $curl;
        $incomingData = json_decode($incomingData, JSON_UNESCAPED_UNICODE);
        /* Получение данных из телеграмм сообщения */
        if (!empty($incomingData)) {
            $this->userName = $incomingData['message']['from']['first_name'];
            $this->chatId = $incomingData['message']['chat']['id'];
            $this->textMessage = $incomingData['message']['text'] ?
                $incomingData['message']['text'] :
                $incomingData['message']['caption'];
            $this->fileId = ($this->messageType == 'photo') ?
                $incomingData['message'][$this->messageType][0]['file_id'] :
                $incomingData['message'][$this->messageType]['file_id'];
            foreach ($this->allowFileType as $type) {
                if (isset($incomingData['message'][$type])) {
                    $this->messageType = $type;
                }
            }
        }
    }

    /**
     * @behavior send file in chat
     * @param $chatID
     * @param $fileID
     * @param $fileType
     * @param false $captionText
     * @throws Exception
     */
    public function sendFile($chatID, $fileID, $fileType, $captionText = false) : void
    {
        if (!in_array($fileType, $this->allowFileType)) {
            throw new Exception('Dont supported this file type');
        }
        $url = $this->telegramUrl . '/send' . ucfirst($this->messageType) . '?chat_id=' . $chatID;

        $url .= $captionText ? '&caption=' . $captionText : '';

        /*
        if ($captionText) {
            $url .= '&caption=' . $captionText;
        } */
        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody([$fileType => $fileID]);
        $this->curl->sendRequest($url, 'POST');
    }

    public function messageHas($pattern, &$matches = false) : bool
    {
        return (bool) preg_match($pattern, $this->textMessage, $matches);
    }

    public function sendMessage($chatId, $message) : void
    {
        $this->curl->sendRequest(
            $this->telegramUrl . '/sendMessage?chat_id=' . $chatId . '&text=' . $message
        );
    }

    public function setWebHook($webHookURL) : void
    {
        $this->curl->sendRequest($this->telegramUrl . '/setWebHook?url=' . $webHookURL);
    }

    public function getChatId(): string
    {
        return $this->chatId;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function getTextFromMessage(): string
    {
        return $this->textMessage;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }
}