<?php
namespace App\Helpers;

/**
 * Array Helper Class
 * توابع کمکی برای آرایه‌ها
 */
class ArrayHelper
{
    /**
     * Get value from array using dot notation
     */
    public static function get($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }
        
        if (is_null($key)) {
            return $array;
        }
        
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        
        $keys = explode('.', $key);
        
        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            
            $array = $array[$segment];
        }
        
        return $array;
    }
    
    /**
     * Set value in array using dot notation
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        
        $keys = explode('.', $key);
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            
            $array = &$array[$key];
        }
        
        $array[array_shift($keys)] = $value;
        
        return $array;
    }
    
    /**
     * Check if key exists in array using dot notation
     */
    public static function has($array, $key)
    {
        if (empty($array) || is_null($key)) {
            return false;
        }
        
        if (array_key_exists($key, $array)) {
            return true;
        }
        
        $keys = explode('.', $key);
        
        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            
            $array = $array[$segment];
        }
        
        return true;
    }
    
    /**
     * Remove key from array using dot notation
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        
        foreach ((array) $keys as $key) {
            $parts = explode('.', $key);
            
            while (count($parts) > 1) {
                $part = array_shift($parts);
                
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            
            unset($array[array_shift($parts)]);
            $array = &$original;
        }
    }
    
    /**
     * Get only specified keys from array
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
    
    /**
     * Get all except specified keys from array
     */
    public static function except($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }
    
    /**
     * Flatten multi-dimensional array
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];
        
        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1 
                    ? array_values($item) 
                    : self::flatten($item, $depth - 1);
                
                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Group array by key
     */
    public static function groupBy($array, $key)
    {
        $result = [];
        
        foreach ($array as $item) {
            $groupKey = is_callable($key) ? $key($item) : self::get($item, $key);
            $result[$groupKey][] = $item;
        }
        
        return $result;
    }
    
    /**
     * Index array by key
     */
    public static function keyBy($array, $key)
    {
        $result = [];
        
        foreach ($array as $item) {
            $indexKey = is_callable($key) ? $key($item) : self::get($item, $key);
            $result[$indexKey] = $item;
        }
        
        return $result;
    }
    
    /**
     * Pluck single column from array
     */
    public static function pluck($array, $value, $key = null)
    {
        $result = [];
        
        foreach ($array as $item) {
            $itemValue = is_callable($value) ? $value($item) : self::get($item, $value);
            
            if (is_null($key)) {
                $result[] = $itemValue;
            } else {
                $itemKey = is_callable($key) ? $key($item) : self::get($item, $key);
                $result[$itemKey] = $itemValue;
            }
        }
        
        return $result;
    }
    
    /**
     * Sort array by key
     */
    public static function sortBy($array, $key, $direction = 'asc')
    {
        usort($array, function ($a, $b) use ($key, $direction) {
            $valueA = is_callable($key) ? $key($a) : self::get($a, $key);
            $valueB = is_callable($key) ? $key($b) : self::get($b, $key);
            
            if ($valueA === $valueB) {
                return 0;
            }
            
            $result = $valueA < $valueB ? -1 : 1;
            
            return $direction === 'desc' ? -$result : $result;
        });
        
        return $array;
    }
    
    /**
     * Filter array by callback
     */
    public static function where($array, $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
    
    /**
     * Get first element matching condition
     */
    public static function first($array, $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? $default : reset($array);
        }
        
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        
        return $default;
    }
    
    /**
     * Get last element matching condition
     */
    public static function last($array, $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? $default : end($array);
        }
        
        return self::first(array_reverse($array, true), $callback, $default);
    }
    
    /**
     * Chunk array into smaller arrays
     */
    public static function chunk($array, $size, $preserveKeys = false)
    {
        return array_chunk($array, $size, $preserveKeys);
    }
    
    /**
     * Merge arrays recursively with distinct values
     */
    public static function merge($array1, $array2)
    {
        $merged = $array1;
        
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        
        return $merged;
    }
    
    /**
     * Convert array to XML
     */
    public static function toXml($array, $rootElement = 'root', $xml = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$rootElement}/>");
        }
        
        foreach ($array as $key => $value) {
            $key = is_numeric($key) ? 'item' : $key;
            
            if (is_array($value)) {
                self::toXml($value, $key, $xml->addChild($key));
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
        
        return $xml->asXML();
    }
    
    /**
     * Convert array to CSV string
     */
    public static function toCsv($array, $delimiter = ',', $enclosure = '"')
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($array as $row) {
            fputcsv($output, $row, $delimiter, $enclosure);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}