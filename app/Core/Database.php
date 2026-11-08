<?php
namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static $instance = null;
    private $connection = null;
    private $config = [];
    private $queries = [];
    private $queryCount = 0;
    private $logger = null;
    
    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->logger = Application::getInstance()->getLogger();
        $this->connect();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect()
    {
        $dbConfig = $this->config['connections']['mysql'];
        
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database'],
                $dbConfig['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']} COLLATE {$dbConfig['collation']}",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ];
            
            $this->connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
            
            // Set timezone
            $this->connection->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            $this->logger->critical('Database connection failed: ' . $e->getMessage());
            throw new Exception\DatabaseException(
                'Database connection failed: ' . $e->getMessage()
            );
        }
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
    
    public function query($sql, $params = [])
    {
        $startTime = microtime(true);
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $executionTime = microtime(true) - $startTime;
            $this->queryCount++;
            
            // Log slow queries
            if ($executionTime > 1) {
                $this->logger->warning('Slow query detected', [
                    'query' => $sql,
                    'params' => $params,
                    'time' => round($executionTime, 4)
                ]);
            }
            
            // Store query for debugging
            if (Application::getInstance()->getConfig('app_debug', false)) {
                $this->queries[] = [
                    'query' => $sql,
                    'params' => $params,
                    'time' => round($executionTime, 4)
                ];
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logger->error('Query failed: ' . $e->getMessage(), [
                'query' => $sql,
                'params' => $params
            ]);
            
            throw new Exception\DatabaseException(
                'Query failed: ' . $e->getMessage(),
                $sql,
                $params
            );
        }
    }
    
    public function select($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function selectColumn($sql, $params = [], $column = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, $column);
    }
    
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        
        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $table,
            implode('`, `', $fields),
            implode(', ', $placeholders)
        );
        
        $this->query($sql, array_values($data));
        
        return $this->connection->lastInsertId();
    }
    
    public function insertBatch($table, $dataArray)
    {
        if (empty($dataArray)) {
            return 0;
        }
        
        $firstRow = reset($dataArray);
        $fields = array_keys($firstRow);
        $placeholders = '(' . implode(', ', array_fill(0, count($fields), '?')) . ')';
        
        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES %s",
            $table,
            implode('`, `', $fields),
            implode(', ', array_fill(0, count($dataArray), $placeholders))
        );
        
        $params = [];
        foreach ($dataArray as $data) {
            foreach ($fields as $field) {
                $params[] = $data[$field] ?? null;
            }
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function update($table, $data, $where, $whereParams = [])
    {
        $fields = array_keys($data);
        $setClause = implode('` = ?, `', $fields) . '` = ?';
        
        $sql = sprintf(
            "UPDATE `%s` SET `%s` WHERE %s",
            $table,
            $setClause,
            $where
        );
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = [])
    {
        $sql = sprintf("DELETE FROM `%s` WHERE %s", $table, $where);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    public function upsert($table, $data, $uniqueKeys)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        
        $updateFields = array_diff($fields, $uniqueKeys);
        $updateClause = implode('`, ` = VALUES(`', $updateFields) . '`)';
        
        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s) ON DUPLICATE KEY UPDATE `%s` = VALUES(`%s",
            $table,
            implode('`, `', $fields),
            implode(', ', $placeholders),
            implode('`, `', $updateFields),
            $updateClause
        );
        
        $stmt = $this->query($sql, array_values($data));
        return $stmt->rowCount();
    }
    
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    public function commit()
    {
        return $this->connection->commit();
    }
    
    public function rollBack()
    {
        return $this->connection->rollBack();
    }
    
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    public function getQueryCount()
    {
        return $this->queryCount;
    }
    
    public function getQueries()
    {
        return $this->queries;
    }
    
    public function tableExists($table)
    {
        $result = $this->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.tables 
             WHERE table_schema = ? AND table_name = ?",
            [$this->config['connections']['mysql']['database'], $table]
        );
        
        return ($result['count'] ?? 0) > 0;
    }
    
    public function getColumns($table)
    {
        return $this->select(
            "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
             FROM information_schema.COLUMNS 
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$this->config['connections']['mysql']['database'], $table]
        );
    }
    
    public function raw($sql, $params = [])
    {
        return $this->query($sql, $params);
    }
    
    public function __destruct()
    {
        $this->connection = null;
    }
    
    private function __clone() {}
}