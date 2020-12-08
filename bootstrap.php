<?php

const ENV_TYPE_BOOL = 0;
const ENV_TYPE_INT  = 1;

function env(string $name, $default = '', int $type = null)
{
    $v = getenv($name, true) ?: getenv($name);
    $v = empty($v) ? $default : $v;
    switch ($type) {
        case ENV_TYPE_BOOL:
            $v = $v === 'true';
            break;
        case ENV_TYPE_INT:
            $v = (int)$v;
            break;
    }
    return $v;
}

require_once __DIR__ . '/config/settings.php';
