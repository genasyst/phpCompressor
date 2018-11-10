<?php
namespace Genasyst\phpCompressor;

class Autoloader
{

    protected static $namespace = 'Genasyst\\phpCompressor';
    private static $registered = false;

    static public function register($prepend = false)
    {
        if (self::$registered === true) {
            return;
        }
        spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        self::$registered = true;
    }

    static public function autoload($class)
    {
        if (0 === strpos($class, self::$namespace . '\\')) {
            $fileName = __DIR__ . strtr(substr($class, strlen(self::$namespace)), '\\', '/') . '.php';
            if (file_exists($fileName)) {
                require_once $fileName;
            } else {
                echo 'File not found' . $fileName;
            }
        }
    }
}
