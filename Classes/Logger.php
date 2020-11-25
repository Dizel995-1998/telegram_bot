<?php

namespace Logger;

class Logger
{
    private static function getFile()
    {
        return $file = fopen('logger.txt', 'a+');
    }

    public static function writeLine($line)
    {
        $time = date("d/m/Y H:i:s");
        $file = fopen('logger.txt', 'a+');
        fwrite($file, '[' . $time . ']' . $line . PHP_EOL);
    }
}