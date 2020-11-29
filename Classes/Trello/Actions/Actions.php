<?php

namespace Trello\Actions;

use Curl\Curl;

class Actions implements ActionInterface
{
    protected static $httpService = null;

    protected static function getHttpService()
    {
        if (self::$httpService == null) {
            self::$httpService = new Curl();
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

    public static function get(string $action, string $id)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, $id, null), 'GET');
        return json_decode(self::getHttpService()->getResponse(), JSON_UNESCAPED_UNICODE);
    }

    public static function update(string $action, string $id, ?array $fields)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, $id, $fields), 'PUT');
        return json_decode(self::getHttpService()->getResponse(), JSON_UNESCAPED_UNICODE);
    }

    public static function delete(string $action, string $id)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, $id, null), 'DELETE');
        return json_decode(self::getHttpService()->getResponse(), JSON_UNESCAPED_UNICODE);
    }

    public static function create(string $action, array $fields)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, null, $fields), 'POST');
        return json_decode(self::getHttpService()->getResponse(), JSON_UNESCAPED_UNICODE);
    }
}