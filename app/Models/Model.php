<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Application;
use JsonSerializable;

/**
 * Base Model Class
 * کلاس پایه برای تمام مدل‌ها
 */
abstract class Model implements JsonSerializable
{
    /**
     * @var Database
     */
    protected $db;
    
    /**
     * @var string Table name
     */
    protected $table;
    
    /**
     * @var string Primary key
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fillable fields
     */
    protected $fillable = [];
    
    /**
     * @var array Hidden fields from serialization
     */
    protected $hidden = [];
    
    /**
     * @var array Field casting types
     */
    protected $casts = [];
    
    /**
     * @var bool Enable timestamps
     */
    protected $timestamps = true;
    
    /**
     * @var string Created at field
     */
    protected $createdAtField = 'created_at';
    
    /**
     * @var string Updated at field
     */
    protected $updatedAtField = 'updated_at';
    
    /**
     * @var bool Enable soft deletes
     */
    protected $softDelete = false;
    
    /**
     * @var string Deleted at field
     */
    protected $deletedAtField = 'deleted_at';
    
    /**
     * @var array Model attributes
     */
    protected $attributes = [];
    
    /**
     * @var array Original attributes
     */
    protected $original = [];
    
    /**
     * @var array Relations to eager load
     */
    protected $with = [];
    
    /**
     * @var array Appended attributes
     */
    protected $appends = [];
    
    /**
     * @var int Results per page
     */
    protected $perPage = 25;
    
    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();
        $this->fill($attributes);
    }
    
    /**
     * Fill model with attributes
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Set attribute value
     */
    public function setAttribute($key, $value)
    {
        // Apply casting
        if (isset($this->casts[$key])) {
            $value = $this->castValue($value, $this->casts[$key]);
        }
        
        $this->attributes[$key] = $value;
        
        return $this;
    }
    
    /**
     * Get attribute value
     */
    public function getAttribute($key)
    {
        // Check for accessor method
        $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        
        if (method_exists($this, $method)) {
            return $this->$method($this->attributes[$key] ?? null);
        }
        
        // Check appended attributes
        if (!isset($this->attributes[$key]) && in_array($key, $this->appends)) {
            $method = 'append' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
            if (method_exists($this, $method)) {
                return $this->$method();
            }
        }
        
        return $this->attributes[$key] ?? null;
    }
    
    /**
     * Magic getter for attributes
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter for attributes
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Cast value to type
     */
    protected function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }
        
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'date':
                return date('Y-m-d', strtotime($value));
            case 'datetime':
                return date('Y-m-d H:i:s', strtotime($value));
            default:
                return $value;
        }
    }
    
    /**
     * Find record by primary key
     */
    public function find($id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        
        if ($this->softDelete) {
            $sql .= " AND `{$this->deletedAtField}` IS NULL";
        }
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Find record or fail
     */
    public function findOrFail($id)
    {
        $result = $this->find($id);
        
        if (!$result) {
            throw new \Exception("Record not found in {$this->table} with {$this->primaryKey} = {$id}");
        }
        
        return $result;
    }
    
    /**
     * Get all records
     */
    public function all($orderBy = null, $order = 'ASC')
    {
        $sql = "SELECT * FROM `{$this->table}`";
        
        if ($this->softDelete) {
            $sql .= " WHERE `{$this->deletedAtField}` IS NULL";
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY `{$orderBy}` {$order}";
        }
        
        $results = $this->db->select($sql);
        
        return $this->hydrate($results);
    }
    
    /**
     * Get records with conditions
     */
    public function where($column, $operator = '=', $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} ?";
        
        if ($this->softDelete) {
            $sql .= " AND `{$this->deletedAtField}` IS NULL";
        }
        
        $results = $this->db->select($sql, [$value]);
        
        return $this->hydrate($results);
    }
    
    /**
     * Get first record matching conditions
     */
    public function firstWhere($column, $operator = '=', $value = null)
    {
        $results = $this->where(...func_get_args());
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Create new record
     */
    public function create(array $data)
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData[$this->createdAtField] = date('Y-m-d H:i:s');
            $filteredData[$this->updatedAtField] = date('Y-m-d H:i:s');
        }
        
        $id = $this->db->insert($this->table, $filteredData);
        
        if ($id) {
            return $this->find($id);
        }
        
        return null;
    }
    
    /**
     * Update record
     */
    public function update(array $data)
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        
        if (!$id) {
            throw new \Exception('Cannot update model without primary key');
        }
        
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData[$this->updatedAtField] = date('Y-m-d H:i:s');
        }
        
        $rowsAffected = $this->db->update(
            $this->table,
            $filteredData,
            "`{$this->primaryKey}` = ?",
            [$id]
        );
        
        if ($rowsAffected > 0) {
            $this->fill(array_merge($this->attributes, $filteredData));
        }
        
        return $rowsAffected;
    }
    
    /**
     * Save model (create or update)
     */
    public function save()
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        
        if ($id) {
            $existing = $this->find($id);
            
            if ($existing) {
                $data = array_diff_assoc($this->attributes, $this->original);
                return $this->update($data);
            }
        }
        
        $result = $this->create($this->attributes);
        
        if ($result) {
            $this->fill($result->toArray());
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete record
     */
    public function delete()
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        
        if (!$id) {
            throw new \Exception('Cannot delete model without primary key');
        }
        
        if ($this->softDelete) {
            return $this->db->update(
                $this->table,
                [$this->deletedAtField => date('Y-m-d H:i:s')],
                "`{$this->primaryKey}` = ?",
                [$id]
            );
        }
        
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }
    
    /**
     * Force delete (even if soft delete is enabled)
     */
    public function forceDelete()
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        
        if (!$id) {
            throw new \Exception('Cannot delete model without primary key');
        }
        
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }
    
    /**
     * Restore soft deleted record
     */
    public function restore()
    {
        if (!$this->softDelete) {
            return false;
        }
        
        $id = $this->attributes[$this->primaryKey] ?? null;
        
        if (!$id) {
            return false;
        }
        
        return $this->db->update(
            $this->table,
            [$this->deletedAtField => null],
            "`{$this->primaryKey}` = ?",
            [$id]
        );
    }
    
    /**
     * Paginate results
     */
    public function paginate($page = 1, $perPage = null)
    {
        $perPage = $perPage ?? $this->perPage;
        $offset = ($page - 1) * $perPage;
        
        $countSql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        $dataSql = "SELECT * FROM `{$this->table}`";
        
        if ($this->softDelete) {
            $countSql .= " WHERE `{$this->deletedAtField}` IS NULL";
            $dataSql .= " WHERE `{$this->deletedAtField}` IS NULL";
        }
        
        $totalResult = $this->db->selectOne($countSql);
        $total = $totalResult['total'] ?? 0;
        
        $dataSql .= " LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->select($dataSql);
        
        return [
            'data' => $this->hydrate($items),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ]
        ];
    }
    
    /**
     * Count records
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        
        if ($this->softDelete) {
            $sql .= " WHERE `{$this->deletedAtField}` IS NULL";
        }
        
        $result = $this->db->selectOne($sql);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Check if record exists
     */
    public function exists($id)
    {
        $result = $this->find($id);
        return $result !== null;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->db->rollBack();
    }
    
    /**
     * Get table name
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * Get primary key
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    
    /**
     * Get primary key value
     */
    public function getId()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }
    
    /**
     * Filter data to only fillable fields
     */
    protected function filterFillable(array $data)
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hydrate results into model instances
     */
    protected function hydrate(array $results)
    {
        $models = [];
        $class = get_called_class();
        
        foreach ($results as $result) {
            $model = new $class();
            $model->fill($result);
            $model->original = $result;
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * Convert model to array
     */
    public function toArray()
    {
        $attributes = $this->attributes;
        
        // Remove hidden fields
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        // Add appended attributes
        foreach ($this->appends as $append) {
            $method = 'append' . str_replace('_', '', ucwords($append, '_')) . 'Attribute';
            if (method_exists($this, $method)) {
                $attributes[$append] = $this->$method();
            }
        }
        
        return $attributes;
    }
    
    /**
     * Convert model to JSON
     */
    public function toJson($options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->toArray(), $options);
    }
    
    /**
     * JsonSerializable implementation
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
    
    /**
     * String representation
     */
    public function __toString()
    {
        return $this->toJson();
    }
    
    /**
     * Static factory method
     */
    public static function query()
    {
        return new static();
    }
}