<?php

namespace TelegramBot;

use Curl\Curl;

class TelegramBot
{
    private array $allowFileType = ['document', 'video', 'audio', 'photo'];
    private string $token;
    private string $telegramUrl = 'https://api.telegram.org/bot';
    private $textMessage;
    private $fileId;
    private $chatId;
    private $userName;
    private string $messageType = 'message';
    private Curl $curl;

    public function __construct($token, $incomingData, Curl $curl)
    {
        $this->token = $token;
        $this->telegramUrl .= $this->token;
        $this->curl = $curl;
        $incomingData = json_decode($incomingData, JSON_UNESCAPED_UNICODE);

        /* Получение данных из телеграмм сообщения */
        $this->userName = $incomingData['message']['from']['first_name'];
        $this->chatId = $incomingData['message']['chat']['id'];

        if (!is_null($incomingData['message']['text'])) {
            $this->textMessage = $incomingData['message']['text'];
        } else {
            $this->textMessage = $incomingData['message']['caption'];

            $arType = ['document', 'audio', 'photo', 'video'];

            foreach ($arType as $type) {
                if (!is_null($incomingData['message'][$type]))
                    $this->messageType = $type;
            }

            if ($this->messageType == 'photo')
                $this->fileId = $incomingData['message'][$this->messageType][0]['file_id'];
            else
                $this->fileId = $incomingData['message'][$this->messageType]['file_id'];
        }
    }

    public function sendFile($chatID, $fileID, $fileType, $captionText = false)
    {
        if (!in_array($fileType, $this->allowFileType))
            throw new Exception("Don't supported this file type");

        $url = $this->telegramUrl . '/send' . ucfirst($this->messageType) . '?chat_id=' . $chatID;

        if ($captionText)
            $url .= '&caption=' . $captionText;

        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody([$this->messageType => $fileID]);
        $this->curl->sendRequest($url, 'POST');
    }

    /**
     * @deprecated
     * @param $chatId
     * @param $fileId
     * @param false $captionText
     * @throws \Exception
     */
    public function sendDocument($chatId, $fileId, $captionText = false)
    {
        $url = $this->telegramUrl . '/sendDocument?chat_id=' . $chatId;

        if ($captionText)
            $url .= '&caption=' . $captionText;

        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody(['document' => $fileId]);
        $this->curl->sendRequest($url, 'POST');
    }

    /**
     * @deprecated
     * @param $chatId
     * @param $fileId
     * @param $captionText
     * @throws \Exception
     */
    public function sendAudio($chatId, $fileId, $captionText)
    {
        $url = $this->telegramUrl . '/sendDocument?chat_id=' . $chatId;

        if ($captionText)
            $url .= '&caption=' . $captionText;

        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody(['audio' => $fileId]);
        $this->curl->sendRequest($url, 'POST');
    }

    /**
     * @deprecated
     * @param $chatId
     * @param $fileId
     * @param false $captionText
     * @throws \Exception
     */
    public function sendPhoto($chatId, $fileId, $captionText = false)
    {
        $url = $this->telegramUrl . '/sendPhoto?chat_id=' . $chatId;

        if ($captionText)
            $url .= '&caption=' . $captionText;

        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody(['photo' => $fileId]);
        $this->curl->sendRequest($url, 'POST');
    }

    /**
     * @deprecated
     * @param $chatId
     * @param $fileId
     * @param false $captionText
     * @throws \Exception
     */
    public function sendVideo($chatId, $fileId, $captionText = false)
    {
        $url = $this->telegramUrl . '/sendVideo?chat_id=' . $chatId;

        if ($captionText)
            $url .= '&caption=' . $captionText;

        $this->curl->setHeaders(['Content-Type: multipart/form-data']);
        $this->curl->withBody(['video' => $fileId]);
        $this->curl->sendRequest($url, 'POST');
    }

    public function messageHas($pattern, &$matches = false)
    {
        return preg_match($pattern, $this->textMessage, $matches);
    }

    public function sendMessage($chatId, $message)
    {
        $this->curl->sendRequest(
            $this->telegramUrl . '/sendMessage?chat_id=' . $chatId . '&text=' . $message
        );
    }

    public function setWebHook($webHookURL)
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