<?php

namespace Core\Trello;


use Core\Trello\Actions\Actions;

class Card
{
    CONST ACTION = 'cards';

    public static function getCard(string $cardID): ?array
    {
        $result = Actions::get('cards', $cardID);
        return $result != 'card not found' ? $result : null;
    }

    public static function getName(string $cardID): ?string
    {
        $result = Actions::get(self::ACTION, $cardID);
        return isset($result['name']) ? $result['name'] : null;
    }

    public static function getDescription(string $cardID): ?string
    {
        $result = Actions::get(self::ACTION, $cardID);
        return $result['desc'] ?? null;
    }

    public static function updateCard(string $cardID, ?array $fields)
    {
        return Actions::update(self::ACTION, $cardID, $fields);
    }

    public static function deleteCard(string $cardID): bool
    {
        return Actions::delete(self::ACTION, $cardID) == 'card not found' ? false : true;
    }

    public static function createCard(string $idList, string $name, ?string $desc, ?string $position)
    {
        $arFields = isset($desc) || isset($position) ?
            ['idList' => $idList, 'name' => $name, 'desc' => $desc, 'pos' => $position] :
            ['idList' => $idList, 'name' => $name];

        return Actions::create(self::ACTION, null, $arFields);
    }

    public static function getCards($boardID)
    {
        return Actions::get(self::ACTION, $boardID);
    }

    public static function getMemberShipsOfCards(string $boardID)
    {
        return Actions::getMemberShipsOfBoard($boardID);
    }

    public static function setLabelOnCard(string $cardID, string $labelID)
    {
        return Actions::create(self::ACTION, $cardID . '/idLabels', ['value' => $labelID]);
    }
}