<?php
namespace App\Helpers;

/**
 * URL Helper Class
 * توابع کمکی برای مدیریت URL
 */
class UrlHelper
{
    /**
     * Build URL with query parameters
     */
    public static function build($path, $params = [])
    {
        $url = rtrim(config('app_url'), '/') . '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }
        
        return $url;
    }
    
    /**
     * Get current URL
     */
    public static function current()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Get current path
     */
    public static function currentPath()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return '/' . trim($path, '/');
    }
    
    /**
     * Get full URL for path
     */
    public static function fullUrl($path)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        return $protocol . '://' . $host . '/' . ltrim($path, '/');
    }
    
    /**
     * Get previous URL
     */
    public static function previous()
    {
        return $_SERVER['HTTP_REFERER'] ?? config('app_url');
    }
    
    /**
     * Check if current URL matches pattern
     */
    public static function is($pattern)
    {
        $path = self::currentPath();
        
        // Convert pattern to regex
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $path) === 1;
    }
    
    /**
     * Parse URL into components
     */
    public static function parse($url)
    {
        return parse_url($url);
    }
    
    /**
     * Get query parameter from URL
     */
    public static function getParam($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get all query parameters
     */
    public static function getParams()
    {
        return $_GET;
    }
    
    /**
     * Add query parameter to URL
     */
    public static function addParam($url, $key, $value)
    {
        $parsed = parse_url($url);
        $query = [];
        
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        
        $query[$key] = $value;
        
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        
        return $scheme . $host . $port . $path . '?' . $queryString . $fragment;
    }
    
    /**
     * Remove query parameter from URL
     */
    public static function removeParam($url, $key)
    {
        $parsed = parse_url($url);
        $query = [];
        
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        
        unset($query[$key]);
        
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        
        $url = $scheme . $host . $port . $path;
        
        if ($queryString) {
            $url .= '?' . $queryString;
        }
        
        if ($fragment) {
            $url .= $fragment;
        }
        
        return $url;
    }
    
    /**
     * Check if URL is secure (HTTPS)
     */
    public static function isSecure($url)
    {
        return strpos($url, 'https://') === 0;
    }
    
    /**
     * Convert URL to HTTPS
     */
    public static function secure($url)
    {
        return str_replace('http://', 'https://', $url);
    }
    
    /**
     * Get domain from URL
     */
    public static function domain($url)
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? '';
    }
    
    /**
     * Get path from URL
     */
    public static function path($url)
    {
        $parsed = parse_url($url);
        return $parsed['path'] ?? '';
    }
    
    /**
     * Get file extension from URL
     */
    public static function extension($url)
    {
        $path = self::path($url);
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }
    
    /**
     * Normalize URL
     */
    public static function normalize($url)
    {
        // Remove trailing slash
        $url = rtrim($url, '/');
        
        // Remove www prefix
        $url = preg_replace('#^https?://www\.#', 'http://', $url);
        
        // Convert to lowercase
        $url = strtolower($url);
        
        return $url;
    }
    
    /**
     * Generate slug for URL
     */
    public static function slug($text)
    {
        return Helper::slugify($text);
    }
    
    /**
     * Redirect to URL
     */
    public static function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * Create pagination URL
     */
    public static function paginate($baseUrl, $page, $params = [])
    {
        $params['page'] = $page;
        return self::build($baseUrl, $params);
    }
}