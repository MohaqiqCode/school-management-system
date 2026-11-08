<?php
namespace App\Core;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function find($id)
    {
        return $this->db->selectOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    public function all($orderBy = null, $order = 'ASC')
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        return $this->db->select($sql);
    }
    
    public function create($data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        if ($this->timestamps) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->insert($this->table, $filteredData);
    }
    
    public function update($id, $data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->update($this->table, $filteredData, "{$this->primaryKey} = ?", [$id]);
    }
    
    public function delete($id)
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }
    
    public function where($column, $operator, $value)
    {
        return $this->db->select(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?",
            [$value]
        );
    }
    
    public function whereIn($column, $values)
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        return $this->db->select(
            "SELECT * FROM {$this->table} WHERE {$column} IN ({$placeholders})",
            $values
        );
    }
    
    public function paginate($page = 1, $perPage = 25)
    {
        $offset = ($page - 1) * $perPage;
        
        $total = $this->db->selectOne("SELECT COUNT(*) as count FROM {$this->table}");
        $items = $this->db->select("SELECT * FROM {$this->table} LIMIT {$perPage} OFFSET {$offset}");
        
        return [
            'data' => $items,
            'total' => $total['count'],
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total['count'] / $perPage)
        ];
    }
}