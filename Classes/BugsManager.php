<?php

namespace BugsManager;

class BugsManager
{
    private \PDO $pdo;

    public function __construct($host, $db, $user, $password)
    {
        $this->pdo = new \PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $password);
    }

    public function getCountBug($countFix = false) : int
    {
        if (!$countFix)
            $data = $this->pdo->query("SELECT COUNT(*) FROM bugs_manager");
        else
            $data = $this->pdo->query("SELECT COUNT(*) FROM bugs_manager WHERE fix_flag is NOT NULL");

        return ($data->fetchColumn());
    }

    public function addNewBug($description_bug)
    {
        $stmt = $this->pdo->prepare("INSERT INTO bugs_manager ( description_bug ) VALUES ( :description_bug )");
        return (bool) $stmt->execute([':description_bug' => $description_bug]);
    }

    public function fixBugID($bugID)
    {
        $stmt = $this->pdo->prepare("UPDATE bugs_manager SET fix_flag = 1 WHERE id = :id");
        return (bool) $stmt->execute([':id' => $bugID]);
    }

    public function getDescriptionByID($bugID)
    {
        $stmt = $this->pdo->prepare("SELECT ( description_bug ) FROM bugs_manager WHERE id = :id");
        $stmt->execute([':id' => $bugID]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllBugs($fixFlag = false)
    {
        if (!$fixFlag) // вернуть не исправленые баги
            $stmt = $this->pdo->query("SELECT id, description_bug FROM bugs_manager WHERE fix_flag is NULL");
        else
            $stmt = $this->pdo->query("SELECT id, description_bug FROM bugs_manager WHERE fix_flag is NOT NULL");

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
}

$bugsManager = new BugsManager('localhost', 'telegram_bot', 'root', 'root');
var_dump($bugsManager->getDescriptionByID(5));