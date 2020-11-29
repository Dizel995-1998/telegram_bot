<?php

namespace Logger;
//require_once 'config/settings.php';

class Logger
{
    protected static $file = null;

    protected static function getLoggerResource()
    {
        if (self::$file == null) {
            self::$file = fopen(LOGGER_FILE, 'a+');
        }
        return self::$file;
    }

    public static function writeLine($line)
    {
        $line = '[' . date("d/m/Y H:i:s") . ']' . $line;
        fwrite(self::getLoggerResource(), $line);
    }
}