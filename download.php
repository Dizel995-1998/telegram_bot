<?php


function downloadFile(string $url, string $filePath, string $fileName)
{
    return (bool) file_put_contents($filePath . $fileName, file_get_contents($url));
}

function getExtensionFileByURL(string $url)
{
    $arUrl = explode('.', basename($url));
    return end($arUrl);
}