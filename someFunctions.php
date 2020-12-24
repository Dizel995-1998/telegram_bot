<?php


use Core\BugsManager;
use Core\Logger;

function downloadFile(string $url, string $filePath, string $fileName)
{
    return (bool) file_put_contents($filePath . $fileName, file_get_contents($url));
}

function getExtensionFileByURL(string $url)
{
    $arUrl = explode('.', basename($url));
    return end($arUrl);
}

function prepareMessage(string $themeTopic, string $author, int $bugNumber, string $messageText) : string
{
    return
         $themeTopic . ' №' . $bugNumber . PHP_EOL .
        'Автор: ' . $author . PHP_EOL .
        'Описание: ' . $messageText . PHP_EOL;
}

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