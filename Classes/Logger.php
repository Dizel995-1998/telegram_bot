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
        fwrite(self::getLoggerResource(), $line . PHP_EOL);
    }

    /**
     * @param $data
     * @param int $type enum 0 - var_dump, 1 - print_r, 2 - var_export
     */
    public static function writeData($data, int $type = 0)
    {
        $line = null;
        switch ($type) {
            case 1:
                $line = ' ' . print_r($data, true);
                break;
            case 2:
                $line = ' ' . var_export($data, true);
                break;
            case 0:
            default:
                ob_start();
                var_dump($data);
                $line = ob_get_contents();
                ob_end_clean();
        }

        self::writeLine($line);
    }
}