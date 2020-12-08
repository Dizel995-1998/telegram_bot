<?php

namespace Core\Trello\Actions;


use GuzzleHttp\Client;

class Actions implements ActionInterface
{
    protected static $httpService = null;

    protected static function getHttpService()
    {
        if (self::$httpService == null) {
            self::$httpService = new Client(['http_errors' => false]);
        }
        return self::$httpService;
    }

    protected static function getTrelloURL() : string
    {
        return 'https://api.trello.com/1/';
    }

    protected static function getToken() : string
    {
        return TRELLO_TOKEN;
    }

    protected static function getKey() : string
    {
        return TRELLO_KEY;
    }

    protected static function prepareUrl(string $action, ?string $id, ?array $fields) : string
    {
        $resultURL = empty($id) ?
            $resultURL = self::getTrelloURL() . $action . '/?key=' . self::getKey() . '&token=' . self::getToken() :
            $resultURL = self::getTrelloURL() . $action . '/' . $id . '?key=' . self::getKey() . '&token=' . self::getToken();

        if (!empty($fields)) {
            foreach ($fields as $field => $value) {
                $resultURL .= '&' . $field . '=' . urlencode($value);
            }
        }
        return $resultURL;
    }

    // TEST
    public static function getMemberShipsOfBoard($boardID)
    {
        $resultURL = self::getTrelloURL() . 'boards/' . $boardID . '/memberships?key=' . self::getKey() . '&token=' . self::getToken();
        $response = self::getHttpService()->get($resultURL);
        return $response->getBody()->getContents();
    }

    public static function get(string $action, string $id) : array
    {
        $response = self::getHttpService()->get(self::prepareUrl($action, $id, null));
        $response = $response->getBody()->getContents();
        return is_string($response) ? json_decode($response, JSON_UNESCAPED_UNICODE) : [];
    }

    public static function update(string $action, string $id, ?array $fields)
    {
        self::getHttpService()->put(self::prepareUrl($action, $id, $fields));
    }

    public static function delete(string $action, string $id)
    {
        self::getHttpService()->delete(self::prepareUrl($action, $id, null));
    }

    public static function create(string $action, array $fields) : bool
    {
        $response = self::getHttpService()->post(self::prepareUrl($action, null, $fields));
        $result = json_decode($response->getBody()->getContents(), JSON_UNESCAPED_UNICODE);
        return (bool) $result == null ? false : true;
    }
}