<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0bdbede424c4d24648bb4566ff75ecb2
{
    public static $files = array (
        'fad373d645dd668e85d44ccf3c38fbd6' => __DIR__ . '/..' . '/guzzlehttp/streams/src/functions.php',
        '154e0d165f5fe76e8e9695179d0a7345' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions.php',
        'a16312f9300fed4a097923eacb0ba814' => __DIR__ . '/..' . '/igorw/get-in/src/get_in.php',
        'bd9ae12176e6d741c5a5bd2efe7b71b7' => __DIR__ . '/..' . '/lstrojny/functional-php/src/Functional/_import.php',
    );

    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GuzzleHttp\\Stream\\' => 18,
            'GuzzleHttp\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GuzzleHttp\\Stream\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/streams/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Symfony\\Component\\Finder\\' => 
            array (
                0 => __DIR__ . '/..' . '/symfony/finder',
            ),
            'Symfony\\Component\\Filesystem\\' => 
            array (
                0 => __DIR__ . '/..' . '/symfony/filesystem',
            ),
            'Symfony\\Component\\DependencyInjection\\' => 
            array (
                0 => __DIR__ . '/..' . '/symfony/dependency-injection',
            ),
            'Symfony\\Component\\Console\\' => 
            array (
                0 => __DIR__ . '/..' . '/symfony/console',
            ),
            'Symfony\\Component\\Config\\' => 
            array (
                0 => __DIR__ . '/..' . '/symfony/config',
            ),
        ),
        'F' => 
        array (
            'Ferrandini' => 
            array (
                0 => __DIR__ . '/..' . '/aferrandini/urlizer',
            ),
        ),
    );

    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/../..' . '/src',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0bdbede424c4d24648bb4566ff75ecb2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0bdbede424c4d24648bb4566ff75ecb2::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit0bdbede424c4d24648bb4566ff75ecb2::$prefixesPsr0;
            $loader->fallbackDirsPsr0 = ComposerStaticInit0bdbede424c4d24648bb4566ff75ecb2::$fallbackDirsPsr0;

        }, null, ClassLoader::class);
    }
}