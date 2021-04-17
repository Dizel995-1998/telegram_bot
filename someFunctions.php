<?php

use Core\BugsManager;
use Core\Logger;

/**
 * Загружает файл по ссылке в директорию filePath с названием файла fileName
 * @param string $url
 * @param string $filePath
 * @param string $fileName
 */
function downloadFile(string $url, string $filePath, string $fileName)
{
    if (!is_dir($filePath)) {
        throw new RuntimeException(sprintf('This is not directory %s', $filePath));
    }

    if (!is_writable($filePath)) {
        throw new RuntimeException(sprintf('Don\'t have enough rights for write to this directory %s', $filePath));
    }

    file_put_contents($filePath . $fileName, file_get_contents($url));
}

function getExtensionFileByURL(string $url)
{
    $arTmp = explode('.', $url);
    return end($arTmp);
}

function prepareMessage(string $themeTopic, string $author, int $bugNumber, string $messageText, $stars = '*') : string
{
    return
        $stars . $themeTopic . ' №' . $bugNumber . $stars . PHP_EOL .
        $stars . 'Автор: ' . $stars . $author . PHP_EOL .
        $stars . 'Описание: ' . $stars . $messageText . PHP_EOL;
}

/**
 * НАРУШЕНИЕ SOLID'a
 */
function downloadImage(string $linkForDownload, int $numberBug, string $fileID)
{
    $extensionFile = getExtensionFileByURL($linkForDownload);
    $fileName = time() . rand(0, 99) . '.' . $extensionFile;

    if (!downloadFile($linkForDownload, DOWNLOAD_DIRECTORY, $fileName)) {
        Logger::writeLine('Не смог загрузить изображение с сервера телеграмма');
        die();
    }

    if (!BugsManager::addFileToBug($numberBug, DOWNLOAD_DIRECTORY . $fileName, $fileID)) {
        Logger::writeLine('Не удалось записать путь к файлам бага');
    }
}