<?php

namespace Logger;

class Logger
{
    private $file;

    private static function getFile()
    {
        return openLoggerFile();
    }

    public static function writeLine($line)
    {
        $time = date("d/m/Y H:i:s");
        fwrite(self::getFile(), '[' . $time . ']' . $line . PHP_EOL);
    }
}