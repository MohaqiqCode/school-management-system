<?php
namespace App\Core;

class Paginator
{
    private $db;
    private $query;
    private $params;
    private $perPage;
    private $total;
    private $pages;
    private $currentPage;
    
    public function __construct($db, $query, $params = [], $perPage = 25)
    {
        $this->db = $db;
        $this->query = $query;
        $this->params = $params;
        $this->perPage = $perPage;
    }
    
    public function getPage($page = 1)
    {
        $this->currentPage = max(1, (int)$page);
        
        // Count total
        $countQuery = $this->buildCountQuery();
        $result = $this->db->selectOne($countQuery, $this->params);
        $this->total = $result['total'] ?? 0;
        
        // Calculate pages
        $this->pages = ceil($this->total / $this->perPage);
        
        // Get items
        $offset = ($this->currentPage - 1) * $this->perPage;
        $dataQuery = $this->query . " LIMIT {$this->perPage} OFFSET {$offset}";
        $items = $this->db->select($dataQuery, $this->params);
        
        return [
            'data' => $items,
            'pagination' => [
                'total' => $this->total,
                'per_page' => $this->perPage,
                'current_page' => $this->currentPage,
                'last_page' => $this->pages,
                'from' => $this->total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $this->perPage, $this->total),
                'has_more_pages' => $this->currentPage < $this->pages,
                'has_previous_page' => $this->currentPage > 1,
                'previous_page' => $this->currentPage > 1 ? $this->currentPage - 1 : null,
                'next_page' => $this->currentPage < $this->pages ? $this->currentPage + 1 : null,
                'first_page' => 1,
                'last_page' => $this->pages,
                'pages' => $this->generatePageNumbers()
            ]
        ];
    }
    
    private function buildCountQuery()
    {
        // Simple count query builder
        // Replace SELECT ... FROM with SELECT COUNT(*) as total FROM
        $query = $this->query;
        
        // Remove ORDER BY, LIMIT, OFFSET clauses
        $query = preg_replace('/ORDER BY.*?(?=LIMIT|$)/i', '', $query);
        $query = preg_replace('/LIMIT.*?(?=OFFSET|$)/i', '', $query);
        $query = preg_replace('/OFFSET.*?$/i', '', $query);
        
        // Remove selected columns
        $pattern = '/SELECT\s+.*?\s+FROM\s/is';
        if (preg_match($pattern, $query)) {
            $query = preg_replace($pattern, 'SELECT COUNT(*) as total FROM ', $query, 1);
        } else {
            $query = 'SELECT COUNT(*) as total FROM (' . $query . ') as count_table';
        }
        
        return $query;
    }
    
    private function generatePageNumbers()
    {
        $pages = [];
        $maxVisible = 7;
        
        if ($this->pages <= $maxVisible) {
            for ($i = 1; $i <= $this->pages; $i++) {
                $pages[] = $i;
            }
        } else {
            $pages[] = 1;
            
            $start = max(2, $this->currentPage - 2);
            $end = min($this->pages - 1, $this->currentPage + 2);
            
            if ($start > 2) {
                $pages[] = '...';
            }
            
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
            
            if ($end < $this->pages - 1) {
                $pages[] = '...';
            }
            
            $pages[] = $this->pages;
        }
        
        return $pages;
    }
    
    public function getTotal()
    {
        return $this->total;
    }
    
    public function getPages()
    {
        return $this->pages;
    }
    
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
}