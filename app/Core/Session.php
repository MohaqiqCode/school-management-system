<?php
namespace App\Core;

class Session
{
    private $flashData = [];
    
    public function __construct()
    {
        // Load flash data
        if (isset($_SESSION['__flash'])) {
            $this->flashData = $_SESSION['__flash'];
            unset($_SESSION['__flash']);
        }
    }
    
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    public function all()
    {
        return $_SESSION;
    }
    
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return array_intersect_key($_SESSION, array_flip($keys));
    }
    
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return array_diff_key($_SESSION, array_flip($keys));
    }
    
    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }
    
    public function flash($key, $value = null)
    {
        if ($value !== null) {
            $_SESSION['__flash'][$key] = $value;
        } else {
            return $this->flashData[$key] ?? null;
        }
    }
    
    public function keep($keys = null)
    {
        if ($keys === null) {
            $_SESSION['__flash'] = array_merge($_SESSION['__flash'] ?? [], $this->flashData);
        } else {
            $keys = is_array($keys) ? $keys : func_get_args();
            foreach ($keys as $key) {
                if (isset($this->flashData[$key])) {
                    $_SESSION['__flash'][$key] = $this->flashData[$key];
                }
            }
        }
    }
    
    public function increment($key, $amount = 1)
    {
        $value = $this->get($key, 0) + $amount;
        $this->set($key, $value);
        return $value;
    }
    
    public function decrement($key, $amount = 1)
    {
        return $this->increment($key, $amount * -1);
    }
    
    public function regenerate($destroy = false)
    {
        return session_regenerate_id($destroy);
    }
    
    public function migrate($destroy = false)
    {
        return $this->regenerate($destroy);
    }
    
    public function destroy()
    {
        session_destroy();
        $_SESSION = [];
    }
    
    public function getId()
    {
        return session_id();
    }
    
    public function setId($id)
    {
        return session_id($id);
    }
    
    public function getName()
    {
        return session_name();
    }
    
    public function age()
    {
        return time() - ($_SESSION['__created_at'] ?? time());
    }
    
    public function isExpired($maxLifetime = null)
    {
        $maxLifetime = $maxLifetime ?? ini_get('session.gc_maxlifetime');
        return $this->age() > $maxLifetime;
    }
    
    public function __destruct()
    {
        // Clean up flash data
    }
}