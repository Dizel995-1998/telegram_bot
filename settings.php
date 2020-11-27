<?php

CONST TOKEN = '1104270168:AAF_MG47TJ5HI5d_t7DK92wejR0Mvy_gBZk';

CONST DATABASE = 'telegram_bot';
CONST USER_DB = 'root';
CONST PASSWORD_DB = 'root';
CONST HOST_DB = 'localhost';

CONST COMMON_GROUP_CHAT_ID = '-1001484960835';
CONST TEST_GROUP_CHAT_ID   = '-1001470007699';
CONST LOGGER_FILE = 'logger.txt';

CONST TRELLO_TOKEN = '010ef0062b53ab1e9b7ac112dca9f805';
CONST TRELLO_KEY = 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';

function createPDOConnect($host, $db, $user, $password)
{
    return new \PDO('mysql:host=' . $host . ';charset=UTF8;dbname=' . $db, $user, $password);
}

function openLoggerFile()
{
    static $file = null;
    if ($file === null) {
        $file = fopen(LOGGER_FILE, 'a+');
    }
    return $file;
}

function getPDOConnection()
{
    static $connect = null;
    if ($connect === null) {
        $connect = createPDOConnect(HOST_DB, DATABASE, USER_DB, PASSWORD_DB);
    }
    return $connect;
}