<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit67338d78ed6686c22fd249ed68b2a037
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Consolety\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Consolety\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit67338d78ed6686c22fd249ed68b2a037::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit67338d78ed6686c22fd249ed68b2a037::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit67338d78ed6686c22fd249ed68b2a037::$classMap;

        }, null, ClassLoader::class);
    }
}
