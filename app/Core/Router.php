<?php
namespace App\Core;

class Router
{
    private $request;
    private $routes = [];
    private $groupStack = [];
    private $middleware = [];
    private $patterns = [
        '{id}' => '([0-9]+)',
        '{slug}' => '([a-zA-Z0-9\-_]+)',
        '{name}' => '([a-zA-Z]+)',
        '{token}' => '([a-zA-Z0-9]+)',
        '{any}' => '([^/]+)',
        '{all}' => '(.*)'
    ];
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function get($uri, $controller, $action = 'index', $middleware = [])
    {
        $this->addRoute('GET', $uri, $controller, $action, $middleware);
        return $this;
    }
    
    public function post($uri, $controller, $action = 'store', $middleware = [])
    {
        $this->addRoute('POST', $uri, $controller, $action, $middleware);
        return $this;
    }
    
    public function put($uri, $controller, $action = 'update', $middleware = [])
    {
        $this->addRoute('PUT', $uri, $controller, $action, $middleware);
        return $this;
    }
    
    public function patch($uri, $controller, $action = 'update', $middleware = [])
    {
        $this->addRoute('PATCH', $uri, $controller, $action, $middleware);
        return $this;
    }
    
    public function delete($uri, $controller, $action = 'delete', $middleware = [])
    {
        $this->addRoute('DELETE', $uri, $controller, $action, $middleware);
        return $this;
    }
    
    public function any($uri, $controller, $action = 'index', $middleware = [])
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $uri, $controller, $action, $middleware);
        }
        return $this;
    }
    
    public function match($methods, $uri, $controller, $action = 'index', $middleware = [])
    {
        foreach ((array)$methods as $method) {
            $this->addRoute(strtoupper($method), $uri, $controller, $action, $middleware);
        }
        return $this;
    }
    
    public function group($attributes, $callback)
    {
        $this->groupStack[] = $attributes;
        
        if (is_callable($callback)) {
            $callback($this);
        }
        
        array_pop($this->groupStack);
    }
    
    public function middleware($middleware)
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);
        return $this;
    }
    
    public function prefix($prefix)
    {
        $this->groupStack[count($this->groupStack) - 1]['prefix'] = $prefix;
        return $this;
    }
    
    public function namespace($namespace)
    {
        $this->groupStack[count($this->groupStack) - 1]['namespace'] = $namespace;
        return $this;
    }
    
    private function addRoute($method, $uri, $controller, $action, $middleware)
    {
        // Apply group attributes
        if (!empty($this->groupStack)) {
            $group = end($this->groupStack);
            
            if (isset($group['prefix'])) {
                $uri = rtrim($group['prefix'], '/') . '/' . ltrim($uri, '/');
            }
            
            if (isset($group['namespace'])) {
                $controller = rtrim($group['namespace'], '\\') . '\\' . $controller;
            }
            
            if (isset($group['middleware'])) {
                $middleware = array_merge((array)$group['middleware'], (array)$middleware);
            }
        }
        
        // Add global middleware
        $middleware = array_merge($this->middleware, (array)$middleware);
        
        $this->routes[] = [
            'method' => $method,
            'uri' => '/' . trim($uri, '/'),
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch()
    {
        $uri = $this->request->getUri();
        $method = $this->request->getMethod();
        
        // Handle method spoofing for PUT/PATCH/DELETE
        if ($method === 'POST') {
            $spoofedMethod = $this->request->post('_method');
            if (in_array(strtoupper($spoofedMethod), ['PUT', 'PATCH', 'DELETE'])) {
                $method = strtoupper($spoofedMethod);
            }
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }
            
            $pattern = $this->compileRoute($route['uri']);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Execute middleware chain
                $middlewareResult = $this->executeMiddleware($route['middleware']);
                
                if ($middlewareResult !== true) {
                    return $middlewareResult;
                }
                
                // Execute controller action
                return $this->executeController(
                    $route['controller'],
                    $route['action'],
                    $matches
                );
            }
        }
        
        // No route matched - 404
        throw new Exception\HttpException('Page not found', 404);
    }
    
    private function compileRoute($uri)
    {
        $pattern = preg_quote($uri, '#');
        
        foreach ($this->patterns as $key => $value) {
            $pattern = str_replace(preg_quote($key), $value, $pattern);
        }
        
        // Optional parameters
        $pattern = preg_replace('/\\\{[^}]+\?\}/', '([^/]*)?', $pattern);
        
        return '#^' . $pattern . '$#';
    }
    
    private function executeMiddleware($middlewareList)
    {
        foreach ($middlewareList as $middleware) {
            // Parse middleware with parameters
            $params = [];
            if (strpos($middleware, ':') !== false) {
                list($middleware, $paramString) = explode(':', $middleware, 2);
                $params = explode(',', $paramString);
            }
            
            $middlewareClass = "App\\Middleware\\{$middleware}";
            
            if (!class_exists($middlewareClass)) {
                throw new \Exception("Middleware '{$middleware}' not found");
            }
            
            $middlewareInstance = new $middlewareClass();
            
            if (method_exists($middlewareInstance, 'handle')) {
                $result = $middlewareInstance->handle(...$params);
                
                if ($result !== true && $result !== null) {
                    return $result;
                }
            }
        }
        
        return true;
    }
    
    private function executeController($controller, $action, $params)
    {
        $controllerClass = "App\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller '{$controller}' not found");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Action '{$action}' not found in controller '{$controller}'");
        }
        
        return call_user_func_array([$controllerInstance, $action], $params);
    }
    
    public function getRoutes()
    {
        return $this->routes;
    }
}