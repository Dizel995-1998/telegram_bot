<?php

namespace BugsManager;

use const Settings\DB_DBNAME;
use const Settings\DB_HOST;
use const Settings\DB_PASSWORD;
use const Settings\DB_USER;

class BugsManager
{
    protected static function createPDOConnect($host, $db, $user, $password)
    {
        return new \PDO('mysql:host=' . $host . ';charset=UTF8;dbname=' . $db, $user, $password);
    }

    private static function getPDOConnection()
    {
        static $connect = null;
        if ($connect === null) {
            $connect = self::createPDOConnect(DB_HOST, DB_DBNAME, DB_USER, DB_PASSWORD);
        }
        return $connect;
    }

    private static function getDbConnection()
    {
        return self::getPDOConnection();
    }

    public static function getCountBugT(bool $countFix = false) : int
    {
        $data = ($countFix) ?
            self::getDbConnection()->query("SELECT COUNT(*) FROM bugs_manager WHERE fix_flag is NOT NULL") :
            self::getDbConnection()->query("SELECT COUNT(*) FROM bugs_manager");
        return $data->fetchColumn();
    }

    public static function getCountBug($all_or_fix = false) : int
    {
        if (!$all_or_fix)
            $data = self::getDbConnection()->query("SELECT COUNT(*) FROM bugs_manager");
        else
            $data = self::getDbConnection()->query("SELECT COUNT(*) FROM bugs_manager WHERE fix_flag is NOT NULL");

        return ($data->fetchColumn());
    }

    public static function addNewBug($description_bug)
    {
        $stmt = self::getDbConnection()->prepare("INSERT INTO bugs_manager ( description_bug ) VALUES ( :description_bug )");
        return (bool) $stmt->execute([':description_bug' => $description_bug]);
    }

    public static function fixBugID($bugID) : bool
    {
        $stmt = self::getDbConnection()->prepare("UPDATE bugs_manager SET fix_flag = 1 WHERE id = :id");
        return (bool) $stmt->execute([':id' => $bugID]);
    }

    public static function getDescriptionByID($bugID)
    {
        $stmt = self::getDbConnection()->prepare("SELECT ( description_bug ) FROM bugs_manager WHERE id = :id");
        $stmt->execute([':id' => $bugID]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function getAllBugs($fixFlag = false)
    {
        if (!$fixFlag) // вернуть массив исправленных багов
            $stmt = self::getDbConnection()->query("SELECT id, description_bug FROM bugs_manager WHERE fix_flag is NULL");
        else
            $stmt = self::getDbConnection()->query("SELECT id, description_bug FROM bugs_manager WHERE fix_flag is NOT NULL");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}