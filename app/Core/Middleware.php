<?php
namespace App\Core;

abstract class Middleware
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = Application::getInstance()->getSession();
    }
    
    abstract public function handle();
}