<?php
namespace App\Traits;

use App\Core\Application;

/**
 * Searchable Trait
 * 
 * تریت جستجوی پیشرفته
 * قابلیت جستجو در فیلدهای مختلف مدل
 * 
 * Usage:
 *   class Student extends Model {
 *       use Searchable;
 *       protected $searchable = ['first_name', 'last_name', 'email', 'student_code'];
 *   }
 * 
 *   $results = Student::search('احمد')->get();
 *   $results = Student::search('احمد', ['email', 'phone'])->get();
 *   $results = Student::searchAdvanced(['name' => 'احمد', 'class_id' => 5])->get();
 */
trait Searchable
{
    /**
     * @var array Fields that are searchable
     */
    protected $searchable = [];

    /**
     * @var string Search query
     */
    protected $searchQuery = '';

    /**
     * @var array Search parameters
     */
    protected $searchParams = [];

    /**
     * @var string Search operator (LIKE, =, >, <, etc.)
     */
    protected $searchOperator = 'LIKE';

    /**
     * @var bool Use full-text search if available
     */
    protected $useFullTextSearch = false;

    /**
     * @var array Relations to search in
     */
    protected $searchableRelations = [];

    /**
     * Search by keyword
     * 
     * @param string $query Search query
     * @param array|null $fields Fields to search in (default: $this->searchable)
     * @return $this
     */
    public static function search($query, $fields = null)
    {
        $instance = new static();
        $instance->searchQuery = trim($query);

        if ($fields !== null) {
            $instance->searchable = (array)$fields;
        }

        return $instance;
    }

    /**
     * Advanced search with multiple criteria
     * 
     * @param array $criteria Associative array of field => value
     * @return $this
     */
    public static function searchAdvanced(array $criteria)
    {
        $instance = new static();
        $instance->searchParams = $criteria;

        return $instance;
    }

    /**
     * Search with LIKE operator
     * 
     * @param string $field Field name
     * @param string $value Search value
     * @return $this
     */
    public static function whereLike($field, $value)
    {
        $instance = new static();
        $instance->searchParams[$field] = ['operator' => 'LIKE', 'value' => "%{$value}%"];

        return $instance;
    }

    /**
     * Search with exact match
     * 
     * @param string $field Field name
     * @param string $value Search value
     * @return $this
     */
    public static function whereExact($field, $value)
    {
        $instance = new static();
        $instance->searchParams[$field] = ['operator' => '=', 'value' => $value];

        return $instance;
    }

    /**
     * Search with IN operator
     * 
     * @param string $field Field name
     * @param array $values Array of values
     * @return $this
     */
    public static function whereIn($field, array $values)
    {
        $instance = new static();
        $instance->searchParams[$field] = ['operator' => 'IN', 'value' => $values];

        return $instance;
    }

    /**
     * Search with BETWEEN operator
     * 
     * @param string $field Field name
     * @param mixed $min Minimum value
     * @param mixed $max Maximum value
     * @return $this
     */
    public static function whereBetween($field, $min, $max)
    {
        $instance = new static();
        $instance->searchParams[$field] = ['operator' => 'BETWEEN', 'value' => [$min, $max]];

        return $instance;
    }

    /**
     * Search with NULL check
     * 
     * @param string $field Field name
     * @param bool $isNull Check for NULL (true) or NOT NULL (false)
     * @return $this
     */
    public static function whereNull($field, $isNull = true)
    {
        $instance = new static();
        $instance->searchParams[$field] = ['operator' => $isNull ? 'IS NULL' : 'IS NOT NULL', 'value' => null];

        return $instance;
    }

    /**
     * Search with date range
     * 
     * @param string $field Date field name
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return $this
     */
    public static function whereDateBetween($field, $startDate, $endDate)
    {
        return static::whereBetween($field, $startDate, $endDate);
    }

    /**
     * Search by month and year
     * 
     * @param string $field Date field name
     * @param int $month Month number
     * @param int $year Year
     * @return $this
     */
    public static function whereMonth($field, $month, $year = null)
    {
        $year = $year ?? date('Y');
        $instance = new static();
        $instance->searchParams["MONTH({$field})"] = ['operator' => '=', 'value' => $month];
        $instance->searchParams["YEAR({$field})"] = ['operator' => '=', 'value' => $year];

        return $instance;
    }

    /**
     * Execute the search and get results
     * 
     * @return array
     */
    public function get()
    {
        $sql = $this->buildSearchQuery();
        $params = $this->buildSearchParams();

        $results = Application::getInstance()->getDB()->select($sql, $params);

        return $this->hydrate($results);
    }

    /**
     * Execute search and get first result
     * 
     * @return static|null
     */
    public function first()
    {
        $results = $this->limit(1)->get();
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Execute search and paginate results
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function paginate($page = 1, $perPage = 25)
    {
        $sql = $this->buildSearchQuery();
        $params = $this->buildSearchParams();

        // Count total
        $countSql = preg_replace('/SELECT.*?FROM/', 'SELECT COUNT(*) as total FROM', $sql, 1);
        $totalResult = Application::getInstance()->getDB()->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;

        // Get data
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        $items = Application::getInstance()->getDB()->select($sql, $params);

        return [
            'data' => $this->hydrate($items),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Add LIMIT to query
     * 
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limitValue = $limit;
        return $this;
    }

    /**
     * Add ORDER BY to query
     * 
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderByColumn = $column;
        $this->orderByDirection = strtoupper($direction);
        return $this;
    }

    /**
     * Count search results
     * 
     * @return int
     */
    public function count()
    {
        $sql = $this->buildSearchQuery();
        $params = $this->buildSearchParams();

        $countSql = preg_replace('/SELECT.*?FROM/', 'SELECT COUNT(*) as total FROM', $sql, 1);
        $result = Application::getInstance()->getDB()->selectOne($countSql, $params);

        return (int)($result['total'] ?? 0);
    }

    /**
     * Check if any results exist
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * Build the search SQL query
     * 
     * @return string
     */
    protected function buildSearchQuery()
    {
        $sql = "SELECT * FROM `{$this->table}`";

        $conditions = [];
        $conditions = array_merge($conditions, $this->buildKeywordConditions());
        $conditions = array_merge($conditions, $this->buildAdvancedConditions());

        // Add soft delete condition
        if ($this->softDelete ?? false) {
            $conditions[] = "`{$this->deletedAtColumn}` IS NULL";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Order by
        if (isset($this->orderByColumn)) {
            $sql .= " ORDER BY `{$this->orderByColumn}` {$this->orderByDirection}";
        }

        // Limit
        if (isset($this->limitValue)) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        return $sql;
    }

    /**
     * Build keyword search conditions
     * 
     * @return array
     */
    protected function buildKeywordConditions()
    {
        $conditions = [];

        if (empty($this->searchQuery) || empty($this->searchable)) {
            return $conditions;
        }

        if ($this->useFullTextSearch) {
            $fullTextFields = implode(', ', array_map(function($f) {
                return "`{$f}`";
            }, $this->searchable));
            $conditions[] = "MATCH({$fullTextFields}) AGAINST(? IN BOOLEAN MODE)";
        } else {
            $likeConditions = [];
            foreach ($this->searchable as $field) {
                $likeConditions[] = "`{$field}` LIKE ?";
            }
            if (!empty($likeConditions)) {
                $conditions[] = '(' . implode(' OR ', $likeConditions) . ')';
            }
        }

        return $conditions;
    }

    /**
     * Build advanced search conditions
     * 
     * @return array
     */
    protected function buildAdvancedConditions()
    {
        $conditions = [];

        foreach ($this->searchParams as $field => $param) {
            if (is_array($param) && isset($param['operator'])) {
                $operator = $param['operator'];
                $value = $param['value'];

                switch (strtoupper($operator)) {
                    case 'LIKE':
                        $conditions[] = "`{$field}` LIKE ?";
                        break;
                    case 'NOT LIKE':
                        $conditions[] = "`{$field}` NOT LIKE ?";
                        break;
                    case '=':
                    case '!=':
                    case '>':
                    case '<':
                    case '>=':
                    case '<=':
                        $conditions[] = "`{$field}` {$operator} ?";
                        break;
                    case 'IN':
                        if (!empty($value)) {
                            $placeholders = implode(',', array_fill(0, count($value), '?'));
                            $conditions[] = "`{$field}` IN ({$placeholders})";
                        }
                        break;
                    case 'NOT IN':
                        if (!empty($value)) {
                            $placeholders = implode(',', array_fill(0, count($value), '?'));
                            $conditions[] = "`{$field}` NOT IN ({$placeholders})";
                        }
                        break;
                    case 'BETWEEN':
                        $conditions[] = "`{$field}` BETWEEN ? AND ?";
                        break;
                    case 'IS NULL':
                        $conditions[] = "`{$field}` IS NULL";
                        break;
                    case 'IS NOT NULL':
                        $conditions[] = "`{$field}` IS NOT NULL";
                        break;
                    default:
                        $conditions[] = "{$field} {$operator} ?";
                }
            } elseif (is_array($param)) {
                // Multiple values for same field (OR condition)
                $orConditions = [];
                foreach ($param as $subParam) {
                    $orConditions[] = "`{$field}` = ?";
                }
                if (!empty($orConditions)) {
                    $conditions[] = '(' . implode(' OR ', $orConditions) . ')';
                }
            } else {
                // Simple equality
                $conditions[] = "`{$field}` = ?";
            }
        }

        return $conditions;
    }

    /**
     * Build search parameters array
     * 
     * @return array
     */
    protected function buildSearchParams()
    {
        $params = [];

        // Keyword search params
        if (!empty($this->searchQuery) && !empty($this->searchable)) {
            if ($this->useFullTextSearch) {
                $params[] = $this->searchQuery;
            } else {
                $searchTerm = "%{$this->searchQuery}%";
                foreach ($this->searchable as $field) {
                    $params[] = $searchTerm;
                }
            }
        }

        // Advanced search params
        foreach ($this->searchParams as $field => $param) {
            if (is_array($param) && isset($param['operator'])) {
                $operator = strtoupper($param['operator']);
                $value = $param['value'];

                switch ($operator) {
                    case 'IN':
                    case 'NOT IN':
                        if (!empty($value)) {
                            $params = array_merge($params, $value);
                        }
                        break;
                    case 'BETWEEN':
                        $params[] = $value[0];
                        $params[] = $value[1];
                        break;
                    case 'IS NULL':
                    case 'IS NOT NULL':
                        // No parameter needed
                        break;
                    default:
                        $params[] = $value;
                }
            } elseif (is_array($param)) {
                $params = array_merge($params, $param);
            } else {
                $params[] = $param;
            }
        }

        return $params;
    }

    /**
     * Enable full-text search
     * 
     * @return $this
     */
    public function useFullText()
    {
        $this->useFullTextSearch = true;
        return $this;
    }

    /**
     * Disable full-text search
     * 
     * @return $this
     */
    public function useLikeSearch()
    {
        $this->useFullTextSearch = false;
        return $this;
    }

    /**
     * Set searchable fields
     * 
     * @param array $fields
     * @return $this
     */
    public function setSearchableFields(array $fields)
    {
        $this->searchable = $fields;
        return $this;
    }

    /**
     * Add a searchable field
     * 
     * @param string $field
     * @return $this
     */
    public function addSearchableField($field)
    {
        if (!in_array($field, $this->searchable)) {
            $this->searchable[] = $field;
        }
        return $this;
    }

    /**
     * Get the generated SQL (for debugging)
     * 
     * @return string
     */
    public function toSql()
    {
        return $this->buildSearchQuery();
    }

    /**
     * Get the search parameters (for debugging)
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->buildSearchParams();
    }

    /**
     * Search in related models
     * 
     * @param string $relation Relation name
     * @param string $query Search query
     * @param array $fields Fields to search in
     * @return $this
     */
    public function whereHas($relation, $query = null, $fields = [])
    {
        // Store relation search criteria
        $this->searchableRelations[$relation] = [
            'query' => $query,
            'fields' => $fields
        ];

        return $this;
    }

    /**
     * Reset search parameters
     * 
     * @return $this
     */
    public function resetSearch()
    {
        $this->searchQuery = '';
        $this->searchParams = [];
        $this->searchableRelations = [];
        $this->limitValue = null;
        $this->orderByColumn = null;
        $this->orderByDirection = null;

        return $this;
    }
}