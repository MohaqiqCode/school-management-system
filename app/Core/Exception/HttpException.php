<?php
namespace App\Core\Exception;

class HttpException extends \Exception
{
    private $statusCode;
    private $headers;
    
    public function __construct($message = '', $statusCode = 400, $headers = [], $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
    
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
}