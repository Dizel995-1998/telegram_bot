<?php

namespace Core\Trello\Actions;

interface ActionInterface
{
    public static function get(string $action, string $id);
    public static function update(string $action, string $id, ?array $fields);
    public static function delete(string $action, string $id);
    public static function create(string $action, array $fields);
}