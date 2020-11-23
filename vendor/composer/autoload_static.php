<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4cbd862ee94adc0c9751bba458336316
{
    public static $prefixLengthsPsr4 = array (
        '\\' => 
        array (
            '\\' => 1,
        ),
        'T' => 
        array (
            'TelegramBot\\' => 12,
            'TelegramBotInterface\\' => 21,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
            'CurlInterface\\' => 14,
        ),
        'B' => 
        array (
            'BugsManager\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        '\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
        'TelegramBot\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Classes',
        ),
        'TelegramBotInterface\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Interfaces',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Classes',
        ),
        'CurlInterface\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Interfaces',
        ),
        'BugsManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4cbd862ee94adc0c9751bba458336316::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4cbd862ee94adc0c9751bba458336316::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4cbd862ee94adc0c9751bba458336316::$classMap;

        }, null, ClassLoader::class);
    }
}
