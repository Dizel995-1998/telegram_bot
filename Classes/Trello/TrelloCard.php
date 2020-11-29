<?php

namespace Trello;
use Trello\Actions\Actions;

class TrelloCard
{
    CONST ACTION = 'cards';

    public function getCard(string $cardID) : ?array
    {
        $result = Actions::get('cards', $cardID);
        return $result != 'card not found' ? $result : null;
    }

    public function getName(string $cardID) : ?string
    {
        $result = Actions::get(self::ACTION, $cardID);
        return isset($result['name']) ? $result['name'] : null;
    }

    public function getDescription(string $cardID) : ?string
    {
        $result = Actions::get(self::ACTION, $cardID);
        return $result['desc'] ?? null;
    }

    public function updateCard(string $cardID, ?array $fields)
    {
        return Actions::update(self::ACTION, $cardID, $fields);
    }

    public function deleteCard(string $cardID) : bool
    {
        return Actions::delete(self::ACTION, $cardID) == 'card not found' ? false : true;
    }

    public function createCard(string $idList, string $name, ?string $desc)
    {
        $arFields = isset($desc) ?
            ['idList' => $idList, 'name' => $name, 'desc' => $desc] :
            ['idList' => $idList, 'name' => $name];

        Actions::create(self::ACTION, $arFields);
    }
}