<?php
namespace App\Core\Exception;

class AuthenticationException extends \Exception
{
    public function __construct($message = 'Unauthenticated', $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}