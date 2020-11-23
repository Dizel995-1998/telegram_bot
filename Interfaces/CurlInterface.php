<?php

namespace CurlInterface;

interface CurlInterface
{
    public function setHeaders($headers = []) : void;
    public function withBody($fields = []) : void;
    public function sendRequest($URL, $method) : void;
    public function getResponse() : string;
}