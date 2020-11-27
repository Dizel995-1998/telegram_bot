<?php

namespace Trello;

class TrelloCard
{
    CONST ACTION = 'cards';

    public function getCard(string $cardID)
    {
        $result = Actions::get('cards', $cardID);
        return $result != 'card not found' ? $result : " ";
    }

    public function getName(string $cardID)
    {
        $result = json_decode(Actions::get(self::ACTION, $cardID), JSON_UNESCAPED_UNICODE);
        return isset($result['name']) ? $result['name'] : false;
    }

    public function getDescription(string $cardID)
    {
        $result = json_decode(Actions::get(self::ACTION, $cardID), JSON_UNESCAPED_UNICODE);
        return isset($result['desc']) ? $result['desc'] : false;
    }

    public function updateCard(string $cardID, ?array $fields)
    {
        return Actions::update('cards', $cardID, $fields);
    }

    public function deleteCard(string $cardID)
    {
        return Actions::delete('cards', $cardID) == 'card not found' ? false : true;
    }
}