<?php

namespace Core\Trello;

use Core\Trello\Actions\Actions;

class Column
{
    CONST ACTION = 'lists';

    public static function getList(string $listID)
    {
        return Actions::get(self::ACTION, $listID);
    }

    public static function getName(string $listID)
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
    public static function updateList(string $listID, array $fields)
    {
        return Actions::update(self::ACTION, $listID, $fields);
    }

    public static function setName(string $listID, string $name)
    {
        Actions::update(self::ACTION, $listID, ['name' => $name]);
    }

    /**
     * @deprecated
     * @param string $listID
     * @param string $idBoard
     */
    public static function changeBoard(string $listID, string $idBoard)
    {
        Actions::update(self::ACTION, $listID, ['idBoard' => $idBoard]);
    }

    public static function createList(string $name, string $idBoard)
    {
        Actions::create(self::ACTION, null, ['name' => $name, 'idBoard' => $idBoard]);
    }
}