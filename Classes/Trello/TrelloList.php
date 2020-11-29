<?php

namespace Trello;
use Trello\Actions\Actions;

class TrelloList
{
    CONST ACTION = 'lists';

    public function getList(string $listID)
    {
        return Actions::get(self::ACTION, $listID);
    }

    public function getName(string $listID)
    {
        $result = Actions::get(self::ACTION, $listID);
        return isset($result['name']) ? $result['name'] : false;
    }

    /**
     * @deprecated
     * @param string $listID
     * @param array $fields
     * @return string
     */
    public function updateList(string $listID, array $fields)
    {
        return Actions::update(self::ACTION, $listID, $fields);
    }

    public function setName(string $listID, string $name)
    {
        Actions::update(self::ACTION, $listID, ['name' => $name]);
    }

    /**
     * @deprecated
     * @param string $listID
     * @param string $idBoard
     */
    public function changeBoard(string $listID, string $idBoard)
    {
        Actions::update(self::ACTION, $listID, ['idBoard' => $idBoard]);
    }

    public function createList(string $name, string $idBoard)
    {
        Actions::create(self::ACTION, ['name' => $name, 'idBoard' => $idBoard]);
    }
}