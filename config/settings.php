<?php

/* Настройки подключения к БД */
CONST DB_DBNAME = 'telegram_bot';
CONST DB_USER = 'root';
CONST DB_PASSWORD = 'root';
CONST DB_HOST = 'localhost';

/* Настройки телеграм бота */
CONST TELEGRAM_BOT_TOKEN = '1104270168:AAF_MG47TJ5HI5d_t7DK92wejR0Mvy_gBZk';
CONST TELEGRAM_FEATURES_CHAT_ID = '-409007151';
CONST TELEGRAM_TEST_GROUP_CHAT_ID  = '-1001470007699';
CONST DESCRIPTION_HOW_WORK_BOT_FOR_TESTERS =
    'В данный чат приходят сообщения от пользователей с описаниями багов, если баг описанный пользователем ' .
    'действительно яв-ся багом, вы можете записать его в доску Trello, ответив на сообщение бага хэштегом #trello, ' .
    'за более подробной информацией о доступных командах бота вы можете использовать команду /getCommands';

CONST DESCRIPTION_HOW_WORK_BOT_USERS =
    'Заметили баг? Напишите #баг или #bug и опишите как можно подробнее, можете также прикрепить к сообщению скриншоты. ' . PHP_EOL . PHP_EOL .
    'Есть идея для новой фичи? Напишите #фича и опишите функционал фичи (пишите всё в одном сообщении).';

CONST TELEGRAM_COMMANDS_LIST =
    '#fixed - при ответе на сообщение с описанием бага отмечает баг как исправленный' . PHP_EOL .
    '#trello #android - при ответе на сообщение с багом добавит баг в Trello доску "Мобильное приложение", колонку "Фидбэк Android"' . PHP_EOL .
    '#trello #ios - при ответе на сообщение с багом добавит баг в Trello доску "Мобильное приложение", колонку "Фидбэк IOS"' . PHP_EOL .
    '#trello #web - при ответе на сообщение с багом добавит баг в Trello доску "05ru Sprints", колонку "Фидбэк Bot"' . PHP_EOL .
    '#trello #фича - при ответе на сообщение с фичей добавит фичу в Trello доску "05ru Sprints", колонку "Бэклог. Требует обсуждения"' . PHP_EOL .
    '/getChatID - возвращает текущий ID чата ( для разрабов )' . PHP_EOL;

CONST TELEGRAM_ANSWER_ON_FIX_BUG  = 'Данный баг был помечен как исправленный, спасибо за содействие в улучшении сайта';

/* Настройки Trello API */
CONST TRELLO_KEY = '010ef0062b53ab1e9b7ac112dca9f805';
CONST TRELLO_TOKEN = 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';

CONST TRELLO_BOARD_FOR_WEB = '05ru Sprints';
CONST TRELLO_COLUMN_FOR_WEB = 'Фидбэк Bot';

CONST TRELLO_BOARD_FOR_ANDROID_IOS = 'Мобильное приложение';
CONST TRELLO_COLUMN_FOR_ANDROID = 'Фидбэк Android';
CONST TRELLO_COLUMN_FOR_IOS = 'Фидбэк IOS';
CONST TRELLO_COLUMN_FOR_FEATURES = 'Бэклог. Требует обсуждения';

/* Настройка логгер файла */
CONST LOGGER_FILE = 'logger.txt';

/* Настройка загрузки файлов приекреплённых к багам ( могут не работать документы и видео ) */
CONST DOWNLOAD_DIRECTORY = 'upload/';
CONST OUR_DOMAIN = 'https://test.linux-electronics.ru/';

