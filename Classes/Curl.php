<?php

namespace Curl;

use CurlInterface\CurlInterface;

class Curl //implements CurlInterface
{
    private $curl;
    private $response;
    private $allowMethods = ['GET', 'PUT', 'POST', 'DELETE'];

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
    }

    public function setHeaders($headers = []) : void
    {
        curl_setopt($this->curl, CURLOPT_HEADER, $headers);
    }

    public function withBody($fields = []) : void
    {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $fields);
    }

    public function sendRequest($URL, $method = 'GET') : void
    {
        if (!in_array($method, $this->allowMethods)) {
            throw new \Exception("Don't supported this method " . $method);
        }

        curl_setopt($this->curl, CURLOPT_URL, $URL);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
        $this->response = curl_exec($this->curl);
    }

    public function getResponse() : string
    {
        return $this->response;
    }
}