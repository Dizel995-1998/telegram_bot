<?php

/* Настройки подключения к БД */
CONST DB_DBNAME = 'telegram_bot';
CONST DB_USER = 'user_05ru';
CONST DB_PASSWORD = 'KtXPCMcz';
CONST DB_HOST = '127.0.0.1';

/* Настройки телеграм бота */
CONST TELEGRAM_BOT_TOKEN = '1104270168:AAHwi4nX3QKeUSd6ERCL-Aa7mLWXUYOXmiU';
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
    '#fixed {комментарий пользователю оставившему запись о баге в телеге} - при ответе на сообщение с описанием бага отмечает баг как исправленный' . PHP_EOL .
    '#trello #android {комментарий к Трелло карточке} - при ответе на сообщение с багом добавит баг в Trello доску "Мобильное приложение", колонку "Фидбэк Android"' . PHP_EOL .
    '#trello #ios {комментарий к Трелло карточке} - при ответе на сообщение с багом добавит баг в Trello доску "Мобильное приложение", колонку "Фидбэк IOS"' . PHP_EOL .
    '#trello #web {комментарий к Трелло карточке} - при ответе на сообщение с багом добавит баг в Trello доску "05ru Sprints", колонку "Фидбэк Bot"' . PHP_EOL .
    '#trello #фича {комментарий к Трелло карточке} - при ответе на сообщение с фичей добавит фичу в Trello доску "05ru Sprints", колонку "Бэклог. Требует обсуждения"' . PHP_EOL .
    '/fixBug {id бага} {комментарий пользователю оставившему запись о баге в телеге} - уведомит пользователя оставившего сообщение о баге, что баг исправлен' . PHP_EOL .
    '/getChatID - возвращает текущий ID чата ( для разрабов )' . PHP_EOL;

CONST TELEGRAM_ANSWER_ON_FIX_BUG  = 'Данный баг был исправлен, спасибо за помощь в улучшении продукта.' . PHP_EOL;

/* Настройки Trello API */
CONST TRELLO_KEY = '010ef0062b53ab1e9b7ac112dca9f805';
CONST TRELLO_TOKEN = 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';

CONST TRELLO_BOARD_FOR_WEB = '05ru Sprints';
CONST TRELLO_COLUMN_FOR_WEB = 'Фидбэк Bot';

CONST TRELLO_BOARD_FOR_ANDROID_IOS = 'Мобильное приложение';
CONST TRELLO_COLUMN_FOR_ANDROID = 'Фидбэк Android';
CONST TRELLO_COLUMN_FOR_IOS = 'Фидбэк IOS';
CONST TRELLO_COLUMN_FOR_FEATURES = 'Бэклог. Требует обсуждения';

CONST TRELLO_BOARD_FOR_MARKETPLACE = 'Маркетплейс 05.ru';
CONST TRELLO_COLUMN_FOR_MARKETPLACE = 'Фидбэк от бота';

/* Настройка логгер файла */
CONST LOGGER_FILE = 'logger.txt';

/* Настройка загрузки файлов приекреплённых к багам ( могут не работать документы и видео ) */
CONST DOWNLOAD_DIRECTORY = 'upload/';
CONST OUR_DOMAIN = 'https://test.05.ru/tg/' . DOWNLOAD_DIRECTORY;

