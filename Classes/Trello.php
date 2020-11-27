<?php

namespace Trello;

use Curl\Curl;

class Trello
{
    private string $trello_url = 'https://api.trello.com/1';
    private string $key;
    private string $token;
    private Curl $curl;

    public function __construct(string $key, string $token, Curl $curl)
    {
        $this->curl = $curl;
        $this->key = $key;
        $this->token = $token;
    }

    /* Методы для работы с карточками */
    public function getCard($cardID)
    {
        $url =
            $this->trello_url . '/cards/' . $cardID . '?key=' . $this->key . '&token=' . $this->token;
        $this->curl->sendRequest($url, 'GET');
        return json_decode($this->curl->getResponse(), JSON_UNESCAPED_UNICODE);
    }

    public function updateCard(string $cardID, ?string $cardDescription, ?string $cardName)
    {
        $url = $this->trello_url . '/cards/' . $cardID . '?key=' . $this->key . '&token=' . $this->token;
        if (!empty($cardDescription)) {
            $url .= '&desc=' . urlencode($cardDescription);
        }
        if (!empty($cardName)) {
            $url .= '&name=' . urlencode($cardName);
        }
        $this->curl->sendRequest($url, 'PUT');
        return $this->curl->getResponse();
    }

    public function deleteCard(string $cardID)
    {
        $url =
            $this->trello_url . '/cards/' . $cardID . '?key=' . $this->key . '&token=' . $this->token;
        $this->curl->sendRequest($url, 'DELETE');
    }

    public function createCard(string $idList, string $cardName, ?string $cardDescription)
    {
        $url =
            $this->trello_url . '/cards?key=' . $this->key . '&token=' .
            $this->token . '&idList=' . $idList . '&name=' . urlencode($cardName);
        if (!empty($cardDescription)) {
            $url .= '&desc=' . urlencode($cardDescription);
        }
        $this->curl->sendRequest($url, 'POST');
        return $this->curl->getResponseCode() == 200;
    }

    /* Методы для работы с листами/колонками */

    public function getList(string $idList)
    {
        $url = $this->trello_url . '/lists/' . $idList . '?key=' . $this->key . '&token=' . $this->token;
        $this->curl->sendRequest($url, 'GET');
        return $this->curl->getResponse();
    }

    public function updateList()
    {
        //$url = $this->trello_url . '/lists/' . $idList . '?key=' . $this->key . '&token=' . $this->token;
       // $this->curl->sendRequest($url, 'GET');
        return $this->curl->getResponse();
    }

    public function deleteList()
    {

    }

    public function createList(string $listName, string $idBoard)
    {
        $url =
            $this->trello_url . '/lists?token=' . $this->token . '&key=' . $this->key .
            '&name=' . urlencode($listName) . '&idBoard=' . urlencode($idBoard);
        $this->curl->sendRequest($url, 'POST');
        return $this->curl->getResponse();
    }

    /**
     * @description Возвращает коллекцию карт в доске ( из всех листов/колонок )
     * @param string $boardID
     * @return string
     * @throws \Exception
     */
    public function getCardsCollection(string $boardID) : array
    {
        $url = $this->trello_url . '/boards/' . $boardID . '/cards?key=' . $this->key . '&token=' . $this->token;
        $this->curl->sendRequest($url, 'GET');
        return json_decode($this->curl->getResponse(), JSON_UNESCAPED_UNICODE);
    }
}
