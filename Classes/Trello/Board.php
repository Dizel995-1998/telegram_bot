<?php

namespace Core\Trello;

use Core\Trello\Actions\Actions;

class Board
{
    CONST ACTIONS = 'boards';

    public static function getListCollection(string $boardID): array
    {
        return Actions::get(self::ACTIONS, $boardID . '/lists');
    }

    public static function getBoardID(string $boardName) : string
    {
        return self::getBoardByBoardName($boardName)['id'] ?? '';
    }

    /**
     * Возвращает массив доски с названием boardName, в случае неудачи возвращается пустой массив
     * @param string $boardName - название доски которую нужно найти
     * @return array
     */
    public static function getBoardByBoardName(string $boardName) : array
    {
        $arResult = [];
        $arBoards = self::getBoardCollection();
        foreach ($arBoards as $board) {
            if ($board['name'] == $boardName) {
                $arResult = $board;
                break;
            }
        }
        return $arResult;
    }

    /**
     * Возвращает коллекцию досок в виде массива
     * @return array
     */
    public static function getBoardCollection(): array
    {
        return Actions::get('members/me/boards', "");
    }

    public static function getListIDbyListName(string $boardID, string $listName) : string
    {
        return self::getListsByListName($boardID, $listName)['id'] ?? '';
    }

    /**
     * Возвращает массив с свойствами колонки, в случае неудачи возвращается пустой массив
     * @param string $boardID - идентификатор доски в которой производится поиск
     * @param string $listName - название листа который нужно найти
     * @return array
     */
    public static function getListsByListName(string $boardID, string $listName) : array
    {
        $arResult = [];
        $arLists = self::getListCollection($boardID);
        foreach ($arLists as $list) {
            if ($list['name'] == $listName) {
                $arResult = $list;
                break;
            }
        }
        return $arResult;
    }

    public static function getLabelsCollection(string $boardID): array
    {
        return Actions::get(self::ACTIONS, $boardID . '/labels');
    }

    public static function getLabelIDbyLabelName(string $boardID, string $labelName) : string
    {
        $labelID = '';
        $arLists = self::getLabelsCollection($boardID);
        foreach ($arLists as $list) {
            if ($list['name'] == $labelName) {
                $labelID = $list['id'];
                break;
            }
        }
        return $labelID;
    }

    public static function getCardsCollection(string $boardID) : array
    {
        return Actions::get(self::ACTIONS, $boardID . '/cards');
    }

    public static function getCardIDbyCardName(string $boardID, string $cardName) : string
    {
        $cardID = '';
        $arCardsCollection = self::getCardsCollection($boardID);
        foreach ($arCardsCollection as $item) {
            if ($cardName == $item['name']) {
                $cardID = $item['id'];
                break;
            }
        }
        return $cardID;
    }

}