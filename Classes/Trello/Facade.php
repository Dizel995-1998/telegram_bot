<?php

namespace Core\Trello;

class Facade
{
    public static function createCard(
        string $boardName, string $listName, string $cardName, string $cardDescription, string $cardPosition
    ) : bool
    {
        $boardID = Board::getBoardID($boardName);
        $idList = Board::getListIDbyListName($boardID, $listName);
        return Card::createCard($idList, $cardName, $cardDescription, $cardPosition);
    }
}