<?php
namespace App\Core;

class Request
{
    private $method;
    private $uri;
    private $queryParams;
    private $postParams;
    private $headers;
    private $body;
    private $files;
    private $server;
    
    public function __construct()
    {
        $this->method = $this->detectMethod();
        $this->uri = $this->detectUri();
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->headers = $this->detectHeaders();
        $this->body = file_get_contents('php://input');
    }
    
    private function detectMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // X-HTTP-Method-Override
        if ($method === 'POST') {
            $override = $this->header('X-HTTP-Method-Override') 
                      ?? $this->post('_method');
            
            if (in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'])) {
                return strtoupper($override);
            }
        }
        
        return $method;
    }
    
    private function detectUri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        
        // Remove base path
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/') {
            $uri = substr($uri, strlen($basePath));
        }
        
        return '/' . trim($uri, '/');
    }
    
    private function detectHeaders()
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function get($key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }
    
    public function post($key, $default = null)
    {
        return $this->postParams[$key] ?? $default;
    }
    
    public function input($key, $default = null)
    {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }
    
    public function all()
    {
        return array_merge($this->queryParams, $this->postParams);
    }
    
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $data = $this->all();
        return array_intersect_key($data, array_flip($keys));
    }
    
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $data = $this->all();
        return array_diff_key($data, array_flip($keys));
    }
    
    public function has($key)
    {
        return isset($this->queryParams[$key]) || isset($this->postParams[$key]);
    }
    
    public function filled($key)
    {
        return !empty($this->input($key));
    }
    
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }
    
    public function hasFile($key)
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }
    
    public function header($key, $default = null)
    {
        $key = strtoupper(str_replace('-', '_', $key));
        return $this->headers[$key] ?? $default;
    }
    
    public function bearerToken()
    {
        $header = $this->header('Authorization');
        
        if ($header && strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        
        return null;
    }
    
    public function getIpAddress()
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['HTTP_X_REAL_IP'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? '127.0.0.1';
    }
    
    public function getUserAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function getJson()
    {
        $body = $this->getBody();
        return json_decode($body, true) ?? [];
    }
    
    public function wantsJson()
    {
        $accept = $this->header('Accept');
        return strpos($accept, '/json') !== false || strpos($accept, '+json') !== false;
    }
    
    public function isAjax()
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    public function isMethod($method)
    {
        return strtoupper($this->method) === strtoupper($method);
    }
    
    public function getPath()
    {
        return $this->uri;
    }
    
    public function getUrl()
    {
        return ($_SERVER['HTTPS'] ?? 'off' === 'on' ? 'https' : 'http') 
               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') 
               . $this->uri;
    }
    
    public function getFullUrl()
    {
        $url = $this->getUrl();
        if ($queryString = http_build_query($this->queryParams)) {
            $url .= '?' . $queryString;
        }
        return $url;
    }
    
    public function previousUrl()
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
    
    public function segment($index)
    {
        $segments = explode('/', trim($this->uri, '/'));
        return $segments[$index] ?? null;
    }
    
    public function segments()
    {
        return explode('/', trim($this->uri, '/'));
    }
}