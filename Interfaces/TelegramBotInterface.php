<?php

namespace TelegramBotInterface;

interface TelegramBotInterface
{
    public function setWebHook($webHookURL);

    public function getChatId(): string;

    public function getUserName(): string;

    public function getMessageType(): string;

    public function getTextFromMessage(): string;

    public function getFileId(): string;

    public function sendMessage($chatId, $message);

    public function sendAudio($chatId, $fileId);

    public function sendDocument($chatId, $fileId);

    public function sendPhoto($chatId, $fileId);

    public function sendVideo($chatId, $fileId);

    public function messageHas($message): bool;
}