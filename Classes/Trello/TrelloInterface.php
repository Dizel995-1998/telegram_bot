<?php

namespace Trello;

interface TrelloInterface
{
    public static function get(string $action, string $id);
    public static function update(string $action, string $id, ?array $fields);
    public static function delete(string $action, string $id);
    public static function create(); // пока не использовать
}