<?php

namespace Trello;

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

    protected static function getToken()
    {
        return 'af6ee7700002364f55f5224edaba230109d366cf22ef74e6c01621491d7b6953';
    }

    protected static function getKey()
    {
        return '010ef0062b53ab1e9b7ac112dca9f805';
    }

    protected static function prepareUrl(string $action, ?string $id, ?array $fields) : string
    {
        $resultURL = '';
        if (!empty($id)) {
            $resultURL = self::getTrelloURL() . $action . '/' . $id . '?key=' . self::getKey() . '&token=' . self::getToken();
        }
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
        return self::getHttpService()->getResponse();
    }

    public static function update(string $action, string $id, ?array $fields)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, $id, $fields), 'PUT');
        return self::getHttpService()->getResponse();
    }

    public static function delete(string $action, string $id)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, $id, null), 'DELETE');
        return self::getHttpService()->getResponse();
    }

    public static function create(string $action, array $fields)
    {
        self::getHttpService()->sendRequest(self::prepareUrl($action, null, $fields), 'POST');
    }
}