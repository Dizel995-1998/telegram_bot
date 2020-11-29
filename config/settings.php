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
сообщением с хэштегом #fixed, сообщение будет передано пользователям с пометкой - исправленно';
CONST TELEGRAM_COMMANDS_LIST = 
'/getInstructions - получить инструкцию по использованию бота' . PHP_EOL .
'/getChatID - вывести ID чата' . PHP_EOL .
'#fixed - при ответе на сообщение с багом, отмечает баг как исправленный' . PHP_EOL .
'/showCountAllBugs - счётчик общего количества багов' . PHP_EOL .
'/showCountFixBugs - количество исправленных багов' . PHP_EOL .
'/fixBug [id] - отметить баг с ID как исправленный' . PHP_EOL .
'/getBug [id] - получить описание бага с ID' . PHP_EOL .
'/getBugs [flag] - если flag = 1, возвращает список исправленных багов, иначе не исправленных' . PHP_EOL;

/* Настройки Trello API */
CONST TRELLO_KEY = '010ef0062b53ab1e9b7ac112dca9f805';
CONST TRELLO_TOKEN = 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';

/* Настройка логгер файла */
CONST LOGGER_FILE = 'logger.txt';


