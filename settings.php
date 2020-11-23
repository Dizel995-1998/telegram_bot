<?php

CONST TOKEN = '1104270168:AAF_MG47TJ5HI5d_t7DK92wejR0Mvy_gBZk';

CONST DATABASE = 'telegram_bot';
CONST USER_DB = 'root';
CONST PASSWORD_DB = 'root';
CONST HOST_DB = 'localhost';

CONST COMMON_GROUP_CHAT_ID = '-1001484960835';
CONST TEST_GROUP_CHAT_ID   = '-1001470007699';

function createPDOConnect($host, $db, $user, $password)
{
    return new \PDO('mysql:host=' . $host . ';charset=UTF8;dbname=' . $db, $user, $password);
}

function getPDOConnection()
{
    static $connect = null;
    if ($connect === null) {
        $connect = createPDOConnect(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
    }
    return $connect;
}