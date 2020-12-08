<?php

/* Настройки подключения к БД */
CONST DB_DBNAME = 'telegram_bot';
CONST DB_USER = 'root';
CONST DB_PASSWORD = 'root';
CONST DB_HOST = 'localhost';

/* Настройки телеграм бота */
CONST TELEGRAM_BOT_TOKEN = '1104270168:AAF_MG47TJ5HI5d_t7DK92wejR0Mvy_gBZk';
CONST TELEGRAM_COMMON_GROUP_CHAT_ID = '-1001484960835';
CONST TELEGRAM_TEST_GROUP_CHAT_ID   = '-1001470007699';
CONST DESCRIPTION_HOW_WORK_BOT =  
'Есть два чата, для обычных пользователей и тестировщиков, все сообщения отмеченные
в чате пользователей с хештэгом #баг будут отправлены к тестировщикам для последующей
диагностики бага. Когда баг будет исправлен, нужно ответить на сообщение бота с описанием бага
сообщением с хэштегом #fixed, сообщение будет передано пользователям с пометкой - исправленно. 
При желании можно переместить баг в доску Trello, для этого ответье на сообщение с описанием бага
хэштегом #trello, об успешности или неуспешности проведённой операции вам будет прислано ответное сообщение';
CONST TELEGRAM_COMMANDS_LIST = 
'/getInstructions - получить инструкцию по использованию бота' . PHP_EOL .
'/getChatID - вывести текущий ID чата' . PHP_EOL .
'#trello - при ответе на сообщение с описанием бага переносит баг доску Trello' . PHP_EOL .
'#fixed - при ответе на сообщение с описанием бага помечает баг как исправленный' . PHP_EOL .
'/fixBug [id] - помечает баг как исправленный';

CONST TELEGRAM_ANSWER_ON_FIX_BUG  = 'Данный баг был помечен как исправленный, спасибо за содействие в улучшении сайта';

/* Настройки Trello API */
CONST TRELLO_KEY = '010ef0062b53ab1e9b7ac112dca9f805';
CONST TRELLO_TOKEN = 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';
CONST OUR_SERVER_DOMAIN = 'https://test.linux-electronics.ru/';
CONST TRELLO_BOARD_NAME = 'hello world';
CONST TRELLO_COLUMN_NAME = 'Тестировщики';

/* Настройка логгер файла */
CONST LOGGER_FILE = 'logger.txt';

/* Настройка загрузки файлов приекреплённых к багам ( работают только фотки, особенность телеги ) */
CONST DOWNLOAD_DIRECTORY = 'upload/';
CONST OUR_DOMAIN = 'https://test.linux-electronics.ru/';


