<?php
namespace App\Core;

class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $body = null;
    private $charset = 'UTF-8';
    
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }
    
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function setHeaders($headers)
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }
    
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }
    
    public function json($data, $statusCode = 200, $options = JSON_UNESCAPED_UNICODE)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json; charset=' . $this->charset);
        $this->setBody(json_encode($data, $options));
        $this->send();
    }
    
    public function html($html, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/html; charset=' . $this->charset);
        $this->setBody($html);
        $this->send();
    }
    
    public function text($text, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/plain; charset=' . $this->charset);
        $this->setBody($text);
        $this->send();
    }
    
    public function xml($xml, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/xml; charset=' . $this->charset);
        $this->setBody($xml);
        $this->send();
    }
    
    public function download($filePath, $filename = null, $headers = [])
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }
        
        $filename = $filename ?? basename($filePath);
        
        $this->setHeaders(array_merge([
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => filesize($filePath),
            'Pragma' => 'public',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0'
        ], $headers));
        
        $this->setBody(file_get_contents($filePath));
        $this->send();
    }
    
    public function stream($callback, $statusCode = 200, $headers = [])
    {
        $this->setStatusCode($statusCode);
        $this->setHeaders($headers);
        
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        if (is_callable($callback)) {
            $callback();
        }
        
        exit;
    }
    
    public function redirect($url, $statusCode = 302)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->send();
    }
    
    public function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    public function with($key, $value)
    {
        $_SESSION['__response'][$key] = $value;
        return $this;
    }
    
    public function withInput()
    {
        $_SESSION['__old_input'] = array_merge($_GET, $_POST);
        return $this;
    }
    
    public function withErrors($errors)
    {
        $_SESSION['__errors'] = $errors;
        return $this;
    }
    
    public function withMessage($type, $message)
    {
        $_SESSION['__flash'][$type] = $message;
        return $this;
    }
    
    public function noCache()
    {
        $this->setHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT'
        ]);
        return $this;
    }
    
    public function send()
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        if ($this->body !== null) {
            echo $this->body;
        }
        
        exit;
    }
}