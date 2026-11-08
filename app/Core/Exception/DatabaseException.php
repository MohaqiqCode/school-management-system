<?php
namespace App\Core\Exception;

class DatabaseException extends \Exception
{
    private $query;
    private $params;
    
    public function __construct($message = 'Database error', $query = '', $params = [], $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
        $this->params = $params;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    public function getParams()
    {
        return $this->params;
    }
}