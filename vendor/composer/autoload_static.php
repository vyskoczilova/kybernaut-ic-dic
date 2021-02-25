<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc8bd793b98f9598fadeedbc3c53c0bd5
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Ibericode\\Vat\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ibericode\\Vat\\' => 
        array (
            0 => __DIR__ . '/..' . '/ibericode/vat/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc8bd793b98f9598fadeedbc3c53c0bd5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc8bd793b98f9598fadeedbc3c53c0bd5::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc8bd793b98f9598fadeedbc3c53c0bd5::$classMap;

        }, null, ClassLoader::class);
    }
}
