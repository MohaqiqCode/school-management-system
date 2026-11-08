<?php
namespace App\Core\Exception;

class ValidationException extends \Exception
{
    private $errors;
    
    public function __construct($message = 'Validation failed', $errors = [], $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}