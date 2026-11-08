<?php
namespace App\Core;

class Autoloader
{
    private $prefixes = [];
    private $classMap = [];
    
    public function register()
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
        
        // Register Composer autoloader if available
        $composerAutoload = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($composerAutoload)) {
            require $composerAutoload;
        }
    }
    
    public function addNamespace($prefix, $baseDir)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        $this->prefixes[$prefix][] = $baseDir;
    }
    
    public function addClassMap($classMap)
    {
        $this->classMap = array_merge($this->classMap, $classMap);
    }
    
    private function loadClass($class)
    {
        // Check class map first
        if (isset($this->classMap[$class])) {
            $file = $this->classMap[$class];
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        
        // Try PSR-4 style
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/../';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        
        // Try registered namespaces
        foreach ($this->prefixes as $prefix => $baseDirs) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) === 0) {
                $relativeClass = substr($class, $len);
                
                foreach ($baseDirs as $baseDir) {
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                    
                    if (file_exists($file)) {
                        require $file;
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}