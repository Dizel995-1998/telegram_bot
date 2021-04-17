<?php

namespace Core\Trello\Actions;

use GuzzleHttp\Client;

class Actions implements ActionInterface
{
    protected static $httpService = null;
    protected static $urlTrello = 'https://api.trello.com/1/';
    protected static $tokenTrello = TRELLO_TOKEN;
    protected static $keyTrello = TRELLO_KEY;
    
    protected static function getHttpService()
    {
        if (self::$httpService == null) {
            self::$httpService = new Client(['http_errors' => false]);
        }
        return self::$httpService;
    }

    protected static function prepareUrl(string $action, ?string $id) : string
    {
        return empty($id) ?
            self::$urlTrello . $action . '/?key=' . self::$keyTrello . '&token=' . self::$tokenTrello :
            self::$urlTrello . $action . '/' . $id . '?key=' . self::$keyTrello . '&token=' . self::$tokenTrello;
    }

    public static function getMemberShipsOfBoard($boardID)
    {
        $resultURL = self::$urlTrello . 'boards/' . $boardID . '/memberships?key=' . self::$keyTrello . '&token=' . self::$tokenTrello;
        $response = self::getHttpService()->get($resultURL);
        return $response->getBody()->getContents();
    }

    public static function get(string $action, string $id) : array
    {
        $response = self::getHttpService()->get(self::prepareUrl($action, $id));
        $body = $response->getBody()->getContents();
        return $response->getStatusCode() == 200 ? json_decode($body, JSON_UNESCAPED_UNICODE) : [];
    }

    public static function update(string $action, string $id, ?array $fields) : bool
    {
         $result = isset($fields) ?
            self::getHttpService()->put(self::prepareUrl($action, $id), ['json' => $fields]) :
            self::getHttpService()->put(self::prepareUrl($action, $id));
         return $result->getStatusCode() == 200;
    }

    public static function delete(string $action, string $id) : bool
    {
        $response = self::getHttpService()->delete(self::prepareUrl($action, $id));
        return $response->getStatusCode() == 200;
    }

    public static function create(string $action, ?string $id, array $fields) : bool
    {
        $response = self::getHttpService()->post(self::prepareUrl($action, $id), ['json' => $fields]);
        $result = json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
        return !((bool)$result == null);
    }
}