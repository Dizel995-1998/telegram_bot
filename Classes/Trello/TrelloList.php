<?php

namespace Trello;

class TrelloList
{
    CONST ACTION = 'lists';

    public function getList(string $listID)
    {
        return Actions::get(self::ACTION, $listID);
    }

    public function getName(string $listID)
    {
        $result = json_decode(Actions::get(self::ACTION, $listID), JSON_UNESCAPED_UNICODE);
        return isset($result['name']) ? $result['name'] : false;
    }

    public function updateList(string $listID, array $fields)
    {
        return Actions::update(self::ACTION, $listID, $fields);
    }

    public function setName(string $listID, string $name)
    {
        Actions::update(self::ACTION, $listID, ['name' => $name]);
    }

    public function setBoard(string $listID, string $idBoard)
    {
        Actions::update(self::ACTION, $listID, ['idBoard' => $idBoard]);
    }
    /*
    public function createList(string $listID, array $fields)
    {
        $requiredFields = ['idBoard', 'name'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $fields)) {
                throw new \Exception('Required fields - idBoard and name');
            }
        }
        return Actions::create('lists', $listID, $fields);
    }
    */
}