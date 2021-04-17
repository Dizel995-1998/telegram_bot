<?php

namespace Core;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TelegramBot
{
    private $allowFileType = [
        "document",
        "video",
        "audio",
        "photo",
        "message"
    ];

    private $token;
    private $telegramUrl = 'https://api.telegram.org/';
    private $textMessage;
    private $messageID;
    private $fileId;
    private $chatId;
    private $userName;
    private $messageType = "message"; // избавиться от инициализации
    private $replyMessageFlag = false;
    private $replyMessageText;
    private $replyMessageID;
    private $errorCode;
    private $errorDescription;
    private $mediaGroupID;
    private $httpService;

    /**
     * @throws Exception if incoming json is empty
     */
    public function __construct(string $token, string $incomingData, Client $httpService)
    {
        $incomingData = json_decode($incomingData, JSON_UNESCAPED_UNICODE);

        if (empty($incomingData)) {
            throw new Exception('Empty json from telegram server');
        }

        $this->token = $token;
        $this->httpService = $httpService;
        $this->userName = $incomingData['message']['from']['first_name'] ?? '';
        $this->chatId = isset($incomingData['message']['chat']['id']) ? (string) $incomingData['message']['chat']['id'] : '';
        $this->textMessage = $incomingData['message']['text'] ?? '';
        $this->textMessage = $incomingData['message']['caption'] ?? $this->textMessage;
        $this->messageID = isset($incomingData['message']['message_id']) ? (string) $incomingData['message']['message_id'] : '';
        $this->replyMessageFlag = isset($incomingData['message']['reply_to_message']);

        if ($this->replyMessageFlag) {
            $this->replyMessageID = $incomingData['message']['reply_to_message']['message_id'] ?? '';
            $this->replyMessageText = (string) isset($incomingData['message']['reply_to_message']['text']) ?
                $incomingData['message']['reply_to_message']['text'] :
                $incomingData['message']['reply_to_message']['caption'];
        }

        foreach ($this->allowFileType as $type) {
            if (isset($incomingData["message"][$type])) {
                $this->messageType = $type;
                break;
            }
        }

        $this->mediaGroupID = $incomingData['message']['media_group_id'] ?? 0;

        if ($this->messageType == "photo") {
            $maxQualityPhotoIndex = count($incomingData["message"]["photo"])-1;
            $this->fileId = (string) $incomingData["message"]["photo"][(string) $maxQualityPhotoIndex]["file_id"];
        } else {
            $this->fileId = (string) $incomingData["message"][(string) $this->messageType]["file_id"];
        }
    }

    /**
     * Возвращает идентификатор группового сообщения
     * ( если сообщение было составным, специфика телеги - дробление сообщения с несколькими файлами на несколько сообщений)
     * @return mixed|string
     */
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
     * @return string
     */
    public function getReplyMessageID() : string
    {
        return (string) $this->replyMessageID;
    }

    /**
     * Отправить сообщение
     * @param string $chatID идентификатор чата куда будет отправлено сообщение
     * @param string $messageType тип сообщения, текст, аудио, видео, документ и т.д
     * @param string|null $messageText текст сообщения
     * @param string|null $replayToMessageID идентификатор сообщения на которое данное сообщение будет отвечать
     * @param string|null $fileID идентификатор файла если мы прикрепляем файл
     * @return bool true в случае успешной отправки
     * @throws GuzzleException
     */
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
        $this->errorDescription = $arResponse['description'] ?? " ";
        $this->errorCode = $arResponse['error_code'] ?? 0;
        return isset($arResponse['ok']);
    }

    /**
     * Возвращает идентификатор пользовательского сообщения
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
     * @return mixed
     * @throws GuzzleException
     */
    public function setWebHook($webHookURL)
    {
        $response = $this->httpService->request('GET',
            $this->telegramUrl . 'bot' . TELEGRAM_BOT_TOKEN . '/setWebHook?url='. $webHookURL);
        return json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Возвращает ID чата в котором пришло сообщение
     * @return string
     */
    public function getChatId() : string
    {
        return (string) $this->chatId;
    }

    /**
     * Возвращает ID файла пользовательского файла
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
        return empty($this->errorCode) ? 0 : $this->errorCode;
    }
    
    /**
     * @description Возвращает ник пользователя отправшего сообщение
     * @return string
     */
    public function getUserName(): string
    {
        return (string) $this->userName;
    }


    /**
     * Получить URL на файл с сервера телеграмма по его ID
     * @param string $fileID
     * @return string|null
     * @throws GuzzleException
     */
    public function getReferenceByFileID(string $fileID) : ?string
    {
        $result = null;
        $url = $this->telegramUrl . 'bot' . $this->token .'/getFile?file_id=' . $fileID;
        $response = $this->httpService->get($url);

        if ($response->getStatusCode() == 200) {
            $response = json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
            $result = $this->telegramUrl . 'file/bot' . $this->token . '/' . $response['result']['file_path'];
        }

        return $result;
    }

    /**
     * Ответ на сообщение
     * @param string $chatID - идентификатор чата в котором необходимо ответить
     * @param string $answerText - текст с ответом
     * @param string $replyToMessageID - сообщение на которое нужно ответить
     * @return bool
     * @throws GuzzleException
     */
    public function answerOnMessageID(string $chatID, string $answerText, string $replyToMessageID) : bool
    {
        $url =
            $this->telegramUrl . 'bot' . $this->token . '/sendMessage?chat_id=' . $chatID .
            '&text=' . urlencode($answerText) . '&reply_to_message_id=' . $replyToMessageID;
        $response = $this->httpService->get($url);
        return $response->getStatusCode() == 200;
    }

    /**
     * Пересылает сообщение из одного чата в другой чат
     * @param string $targetChatID - чат куда будет отправлено сообщение
     * @param string $fromChatID - исходный чат откуда будет отправлено сообщение
     * @param string $messageID - идентификатор сообщения который будет переправлен в чат targetChatID
     * @throws GuzzleException
     */
    public function forwardMessage(string $targetChatID, string $fromChatID, string $messageID)
    {
        $url = $this->telegramUrl . 'bot' . $this->token . '/forwardMessage?chat_id=' . $targetChatID .
            '&from_chat_id=' . $fromChatID . '&message_id=' . $messageID;
        $this->httpService->get($url);
    }
}