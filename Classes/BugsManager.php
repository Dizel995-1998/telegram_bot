<?php

namespace BugsManager;

class BugsManager
{
    private static function getDbConnection()
    {
        return getPDOConnection();
    }

    public static function getCountBugT($countFix = false) : int
    {
        if (!$countFix) {
            $data = self::getDbConnection()->query("SELECT COUNT(*) FROM bugs_manager");
        } else {
            $data = self::getDbConnection()->query("SELECT COUNT(*) FROM bugs_manager WHERE fix_flag is NOT NULL");
        }
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

    public static function fixBugID($bugID)
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