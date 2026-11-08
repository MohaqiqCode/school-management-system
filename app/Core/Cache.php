<?php
namespace App\Core;

class Cache
{
    private $cachePath;
    private $defaultTtl = 3600;
    
    public function __construct($cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/') . '/';
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    public function get($key, $default = null)
    {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data === false) {
            return $default;
        }
        
        if ($data['expires_at'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $file = $this->getCacheFile($key);
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    public function has($key)
    {
        return $this->get($key) !== null;
    }
    
    public function delete($key)
    {
        $file = $this->getCacheFile($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    public function flush()
    {
        $files = glob($this->cachePath . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
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
    
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        
        return $result;
    }
    
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        
        return true;
    }
    
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        
        return true;
    }
    
    private function getCacheFile($key)
    {
        return $this->cachePath . md5($key) . '.cache';
    }
}