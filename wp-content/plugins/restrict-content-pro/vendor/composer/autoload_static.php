<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3d829276b59b5e5367fb432199f62178
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RCP\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RCP\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3d829276b59b5e5367fb432199f62178::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3d829276b59b5e5367fb432199f62178::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3d829276b59b5e5367fb432199f62178::$classMap;

        }, null, ClassLoader::class);
    }
}
