<?php

require_once 'vendor/autoload.php';

use FileManager\FileManager;

$file = new FileManager("file.txt");

$file->reWriteNumber($file->getNumber()+1);
var_dump($file->getNumber());

die();

$file = fopen('file.txt', 'r+');

var_dump(fread($file, filesize('file.txt')));
fwrite($file, '123456');

/*
var_dump($file->getNumber());
$file->reWriteNumber(123);
$file->reWriteNumber(123);
var_dump($file->getNumber());


die();

$file = fopen('file.txt', 'r+');

fseek($file, 0);
var_dump(fgets($file));
file_put_contents('file.txt', '');
fwrite($file, 654321);
*/