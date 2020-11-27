<?php

namespace Logger;

use const Settings\LOGGER_FILE;

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