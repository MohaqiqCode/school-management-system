<?php
namespace App\Core;

class Logger
{
    private $logPath;
    private $minLevel;
    
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    
    const LEVELS = [
        self::EMERGENCY => 0,
        self::ALERT => 1,
        self::CRITICAL => 2,
        self::ERROR => 3,
        self::WARNING => 4,
        self::NOTICE => 5,
        self::INFO => 6,
        self::DEBUG => 7,
    ];
    
    public function __construct($logPath, $minLevel = null)
    {
        $this->logPath = rtrim($logPath, '/') . '/';
        $this->minLevel = $minLevel ?? self::DEBUG;
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public function emergency($message, $context = [])
    {
        $this->log(self::EMERGENCY, $message, $context);
    }
    
    public function alert($message, $context = [])
    {
        $this->log(self::ALERT, $message, $context);
    }
    
    public function critical($message, $context = [])
    {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    public function error($message, $context = [])
    {
        $this->log(self::ERROR, $message, $context);
    }
    
    public function warning($message, $context = [])
    {
        $this->log(self::WARNING, $message, $context);
    }
    
    public function notice($message, $context = [])
    {
        $this->log(self::NOTICE, $message, $context);
    }
    
    public function info($message, $context = [])
    {
        $this->log(self::INFO, $message, $context);
    }
    
    public function debug($message, $context = [])
    {
        $this->log(self::DEBUG, $message, $context);
    }
    
    private function log($level, $message, $context = [])
    {
        if (self::LEVELS[$level] > self::LEVELS[$this->minLevel]) {
            return;
        }
        
        $logFile = $this->logPath . date('Y-m-d') . '.log';
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $jsonEntry = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        file_put_contents($logFile, $jsonEntry, FILE_APPEND | LOCK_EX);
        
        // Also store in database for system_logs table
        if (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR])) {
            try {
                $db = Database::getInstance();
                $db->insert('system_logs', [
                    'level' => $level,
                    'module' => $context['module'] ?? 'system',
                    'message' => $message,
                    'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Silently fail if database is not available
            }
        }
    }
}