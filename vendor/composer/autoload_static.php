<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit05c03217b802959b3fb565b9055254ad
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Glacom\\GlacomFunctions\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Glacom\\GlacomFunctions\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit05c03217b802959b3fb565b9055254ad::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit05c03217b802959b3fb565b9055254ad::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit05c03217b802959b3fb565b9055254ad::$classMap;

        }, null, ClassLoader::class);
    }
}
