<?php
namespace App\Core;

class Config
{
    private static $config = [];
    private static $loaded = false;
    
    public static function load()
    {
        if (self::$loaded) {
            return;
        }
        
        $configPath = Application::getInstance()->getBasePath() . '/config/';
        
        self::$config['app'] = require $configPath . 'app.php';
        self::$config['database'] = require $configPath . 'database.php';
        self::$config['permissions'] = require $configPath . 'permissions.php';
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null)
    {
        self::load();
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    public static function set($key, $value)
    {
        self::load();
        
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }
        
        $config = $value;
    }
    
    public static function all()
    {
        self::load();
        return self::$config;
    }
    
    public static function has($key)
    {
        return self::get($key) !== null;
    }
}