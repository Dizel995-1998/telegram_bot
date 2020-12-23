<?php

namespace Core;

class BugsManager
{
    private static $pdo = null;

    protected static function getPDOconnection()
    {
        if (self::$pdo == null) {
            self::$pdo = new \PDO('mysql:host=' . 'localhost' . ';charset=UTF8;dbname=' . 'telegram_bot', 'root', 'root');
        }
        return self::$pdo;
    }

    /**
     * Создаёт обьект ( запись ) сущности bug
     * @param string $bugDescription - описание бага
     * @param string $bugAuthor - пользователь заметивший баг
     * @param string $messageID
     * @param string $chatID
     * @param string|null $messageGroupID
     * @return bool - возвращает true, в случае успешной записи бага в БД
     */
    public static function addRowToBugs(string $bugDescription, string $bugAuthor, string $messageID, string $chatID, ?string $messageGroupID) : bool
    {
        $query = 'INSERT INTO bugs (bug_description, bug_author, message_id, message_group_id, chat_id)
                  VALUES ( :bug_description, :bug_author, :message_id, :message_group_id, :chat_id)';
        return (bool) self::getPDOconnection()
            ->prepare($query)
            ->execute([':bug_description' => $bugDescription, ':bug_author' => $bugAuthor,
                       ':message_group_id' => $messageGroupID, ':message_id' => $messageID,
                       ':chat_id' => $chatID]);
    }

    /**
     * Отмечает баг с bugID как исправленный
     * @param int $bugID - идентификатор бага который необходимо отметить как исправленный
     * @return bool - возвращает true в случае успешного изменения статуса записи
     */
    public static function bugFix(int $bugID) : bool
    {
        return (bool) self::getPDOconnection()
            ->prepare('UPDATE bugs SET bug_fix = 1 WHERE bug_id = :bug_id')
            ->execute([':bug_id' => $bugID]);
    }

    /**
     * Добавляет файл к описанию бага
     * @param int $bugID - идентификатор бага к которому нужно добавить файл
     * @param string $pathToFile - путь к файлу
     * @param string $fileID
     * @return bool - возвращает true в случае успешного добавления записи
     */
    public static function addFileToBug(int $bugID, string $pathToFile, string $fileID) : bool
    {
        return (bool) self::getPDOconnection()
            ->prepare('INSERT INTO files ( bug_id, path_to_file, file_id ) VALUES (:bug_id, :path_to_file, :file_id)')
            ->execute([':bug_id' => $bugID, ':path_to_file' => $pathToFile, ':file_id' => $fileID]);
    }

    /**
     * Возвращает ид бага по messageGroupID
     * @param string $messageGroupID - ид группы по которому нужно найти ид бага
     * @return string
     */
    public static function getBugIDbyMessageGroupID(string $messageGroupID) : string
    {
        $obResult = self::getPDOconnection()
            ->prepare('SELECT bug_id FROM bugs WHERE message_group_id = :message_group_id');
        $obResult->execute([':message_group_id' => $messageGroupID]);
        return $obResult->fetch(\PDO::FETCH_COLUMN) ?? "";
    }

    /**
     * Возвращает id последнего добавленного бага 
     * @return int
     */
    public static function getLastBugID() : int
    {
        return self::getPDOconnection()
            ->query('SELECT MAX(bug_id) FROM bugs')
            ->fetchColumn();
    }

    /**
     * Возвращает описание бага по его ID
     * @param int $bugID
     * @return string
     */
    public static function getDescriptionByBugID(int $bugID) : string
    {
        $obResult = self::getPDOconnection()->prepare(
            'SELECT bug_description FROM bugs WHERE bug_id = :bug_id');
        $obResult->execute([':bug_id' => $bugID]);
        return $obResult->fetchColumn();
    }

    /**
     * Возвращает массив с строками путей к файлам бага
     * @param int $bugID
     * @return array
     */
    public static function getPathToFilesByBugID(int $bugID) : array
    {
        $obResult = self::getPDOconnection()->prepare(
            'SELECT path_to_file FROM bugs JOIN files ON bugs.bug_id = files.bug_id WHERE bugs.bug_id = :bug_id');
        $obResult->execute([':bug_id' => $bugID]);
        return $obResult->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function getFilesIDbyBugID(int $bugID)
    {
        $obResult = self::getPDOconnection()->prepare(
            'SELECT file_id FROM files WHERE bug_id = :bug_id');
        $obResult->execute([':bug_id' => $bugID]);
        return $obResult->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function getAllInformationAboutBug(int $bugID)
    {
        $obResult = self::getPDOconnection()->prepare(
            'SELECT bug_description, bug_author, bug_fix, message_group_id, message_id, chat_id FROM bugs WHERE bug_id = :bug_id');
        $obResult->execute([':bug_id' => $bugID]);
        $result = $obResult->fetch(\PDO::FETCH_ASSOC);
        if (is_bool($result)) {
            return [];
        } else {
            return $result;
        }
    }
}
