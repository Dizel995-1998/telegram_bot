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

    public static function addLabelOnCard(string $boardName, string $cardName, string $labelName) : bool
    {
        $boardID = Board::getBoardID($boardName);
        $cardID = Board::getCardIDbyCardName($boardID, $cardName);
        $labelID = Board::getLabelIDbyLabelName($boardID, $labelName);
        return Card::setLabelOnCard($cardID, $labelID);
    }
}