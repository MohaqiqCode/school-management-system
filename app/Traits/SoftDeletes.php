<?php
namespace App\Traits;

use App\Core\Application;

/**
 * SoftDeletes Trait
 * 
 * تریت حذف منطقی (Soft Delete)
 * به جای حذف فیزیکی رکورد، آن را با یک timestamp علامت‌گذاری می‌کند
 * 
 * Usage:
 *   class User extends Model {
 *       use SoftDeletes;
 *       protected $softDelete = true;
 *   }
 * 
 *   $user->delete();        // Soft delete
 *   $user->forceDelete();   // Hard delete
 *   $user->restore();       // Restore soft deleted record
 *   User::withTrashed()->get();  // Include trashed records
 *   User::onlyTrashed()->get();  // Only trashed records
 */
trait SoftDeletes
{
    /**
     * @var bool Enable soft deletes
     */
    protected $softDelete = true;

    /**
     * @var string Deleted at column name
     */
    protected $deletedAtColumn = 'deleted_at';

    /**
     * @var bool Include trashed records in queries
     */
    protected static $withTrashed = false;

    /**
     * @var bool Only trashed records in queries
     */
    protected static $onlyTrashed = false;

    /**
     * Boot the trait
     */
    protected function bootSoftDeletes()
    {
        // Add global scope to exclude trashed records
        if ($this->softDelete && !static::$withTrashed && !static::$onlyTrashed) {
            // This is handled in the query methods
        }
    }

    /**
     * Delete the model (soft delete)
     * 
     * @return bool
     */
    public function delete()
    {
        if (!$this->softDelete) {
            return $this->forceDelete();
        }

        $id = $this->attributes[$this->primaryKey] ?? null;
        if (!$id) return false;

        // Check if already deleted
        if ($this->isTrashed()) {
            return true;
        }

        $result = Application::getInstance()->getDB()->update(
            $this->table,
            [
                $this->deletedAtColumn => date('Y-m-d H:i:s'),
                $this->updatedAtField => date('Y-m-d H:i:s')
            ],
            "{$this->primaryKey} = ?",
            [$id]
        );

        if ($result) {
            $this->attributes[$this->deletedAtColumn] = date('Y-m-d H:i:s');
        }

        return $result > 0;
    }

    /**
     * Force delete the model (hard delete)
     * 
     * @return bool
     */
    public function forceDelete()
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        if (!$id) return false;

        return Application::getInstance()->getDB()->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        ) > 0;
    }

    /**
     * Restore a soft deleted model
     * 
     * @return bool
     */
    public function restore()
    {
        if (!$this->softDelete) return false;

        $id = $this->attributes[$this->primaryKey] ?? null;
        if (!$id) return false;

        if (!$this->isTrashed()) return true;

        $result = Application::getInstance()->getDB()->update(
            $this->table,
            [
                $this->deletedAtColumn => null,
                $this->updatedAtField => date('Y-m-d H:i:s')
            ],
            "{$this->primaryKey} = ?",
            [$id]
        );

        if ($result) {
            $this->attributes[$this->deletedAtColumn] = null;
        }

        return $result > 0;
    }

    /**
     * Check if model is trashed
     * 
     * @return bool
     */
    public function isTrashed()
    {
        $deletedAt = $this->attributes[$this->deletedAtColumn] ?? null;
        return !is_null($deletedAt);
    }

    /**
     * Check if model is not trashed
     * 
     * @return bool
     */
    public function isNotTrashed()
    {
        return !$this->isTrashed();
    }

    /**
     * Get the deleted at timestamp
     * 
     * @return string|null
     */
    public function deletedAt()
    {
        return $this->attributes[$this->deletedAtColumn] ?? null;
    }

    /**
     * Get all records including trashed
     * 
     * @return array
     */
    public static function withTrashed()
    {
        static::$withTrashed = true;
        static::$onlyTrashed = false;

        $instance = new static();
        $result = $instance->all();

        static::$withTrashed = false;

        return $result;
    }

    /**
     * Get only trashed records
     * 
     * @return array
     */
    public static function onlyTrashed()
    {
        static::$onlyTrashed = true;
        static::$withTrashed = false;

        $instance = new static();
        $db = Application::getInstance()->getDB();

        $result = $db->select(
            "SELECT * FROM `{$instance->table}` WHERE `{$instance->deletedAtColumn}` IS NOT NULL"
        );

        static::$onlyTrashed = false;

        return $instance->hydrate($result);
    }

    /**
     * Find a record by ID (including trashed if specified)
     * 
     * @param int|string $id
     * @return static|null
     */
    public function find($id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";

        if ($this->softDelete && !static::$withTrashed && !static::$onlyTrashed) {
            $sql .= " AND `{$this->deletedAtColumn}` IS NULL";
        } elseif (static::$onlyTrashed) {
            $sql .= " AND `{$this->deletedAtColumn}` IS NOT NULL";
        }

        $result = Application::getInstance()->getDB()->selectOne($sql, [$id]);

        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }

        return null;
    }

    /**
     * Get all records
     * 
     * @param string|null $orderBy
     * @param string $order
     * @return array
     */
    public function all($orderBy = null, $order = 'ASC')
    {
        $sql = "SELECT * FROM `{$this->table}`";

        if ($this->softDelete && !static::$withTrashed && !static::$onlyTrashed) {
            $sql .= " WHERE `{$this->deletedAtColumn}` IS NULL";
        } elseif (static::$onlyTrashed) {
            $sql .= " WHERE `{$this->deletedAtColumn}` IS NOT NULL";
        }

        if ($orderBy) {
            $sql .= " ORDER BY `{$orderBy}` {$order}";
        }

        $results = Application::getInstance()->getDB()->select($sql);

        return $this->hydrate($results);
    }

    /**
     * Count records
     * 
     * @param string|null $where
     * @param array $params
     * @return int
     */
    public function count($where = null, $params = [])
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";

        $conditions = [];

        if ($this->softDelete && !static::$withTrashed && !static::$onlyTrashed) {
            $conditions[] = "`{$this->deletedAtColumn}` IS NULL";
        } elseif (static::$onlyTrashed) {
            $conditions[] = "`{$this->deletedAtColumn}` IS NOT NULL";
        }

        if ($where) {
            $conditions[] = $where;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $result = Application::getInstance()->getDB()->selectOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Check if record exists
     * 
     * @param int|string $id
     * @param bool $withTrashed Include trashed records
     * @return bool
     */
    public function exists($id, $withTrashed = false)
    {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";

        if ($this->softDelete && !$withTrashed) {
            $sql .= " AND `{$this->deletedAtColumn}` IS NULL";
        }

        $result = Application::getInstance()->getDB()->selectOne($sql, [$id]);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Check if a record is trashed by ID
     * 
     * @param int|string $id
     * @return bool
     */
    public function isTrashedById($id)
    {
        if (!$this->softDelete) return false;

        $result = Application::getInstance()->getDB()->selectOne(
            "SELECT `{$this->deletedAtColumn}` FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );

        return !is_null($result[$this->deletedAtColumn] ?? null);
    }

    /**
     * Restore a record by ID
     * 
     * @param int|string $id
     * @return bool
     */
    public function restoreById($id)
    {
        if (!$this->softDelete) return false;

        return Application::getInstance()->getDB()->update(
            $this->table,
            [
                $this->deletedAtColumn => null,
                $this->updatedAtField => date('Y-m-d H:i:s')
            ],
            "{$this->primaryKey} = ?",
            [$id]
        ) > 0;
    }

    /**
     * Bulk restore records by IDs
     * 
     * @param array $ids
     * @return int Number of restored records
     */
    public function bulkRestore(array $ids)
    {
        if (!$this->softDelete || empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return Application::getInstance()->getDB()->update(
            $this->table,
            [
                $this->deletedAtColumn => null,
                $this->updatedAtField => date('Y-m-d H:i:s')
            ],
            "{$this->primaryKey} IN ({$placeholders})",
            $ids
        );
    }

    /**
     * Bulk soft delete records by IDs
     * 
     * @param array $ids
     * @return int Number of deleted records
     */
    public function bulkDelete(array $ids)
    {
        if (!$this->softDelete || empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return Application::getInstance()->getDB()->update(
            $this->table,
            [
                $this->deletedAtColumn => date('Y-m-d H:i:s'),
                $this->updatedAtField => date('Y-m-d H:i:s')
            ],
            "{$this->primaryKey} IN ({$placeholders})",
            $ids
        );
    }

    /**
     * Bulk force delete records by IDs
     * 
     * @param array $ids
     * @return int Number of deleted records
     */
    public function bulkForceDelete(array $ids)
    {
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return Application::getInstance()->getDB()->delete(
            $this->table,
            "{$this->primaryKey} IN ({$placeholders})",
            $ids
        );
    }

    /**
     * Permanently delete all trashed records
     * 
     * @return int Number of deleted records
     */
    public function purgeTrashed()
    {
        if (!$this->softDelete) return 0;

        return Application::getInstance()->getDB()->delete(
            $this->table,
            "`{$this->deletedAtColumn}` IS NOT NULL"
        );
    }

    /**
     * Get the number of trashed records
     * 
     * @return int
     */
    public function trashedCount()
    {
        if (!$this->softDelete) return 0;

        $result = Application::getInstance()->getDB()->selectOne(
            "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `{$this->deletedAtColumn}` IS NOT NULL"
        );

        return (int)($result['count'] ?? 0);
    }

    /**
     * Get the number of active (non-trashed) records
     * 
     * @return int
     */
    public function activeCount()
    {
        if (!$this->softDelete) {
            return $this->count();
        }

        $result = Application::getInstance()->getDB()->selectOne(
            "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `{$this->deletedAtColumn}` IS NULL"
        );

        return (int)($result['count'] ?? 0);
    }

    /**
     * Check if soft deletes are enabled
     * 
     * @return bool
     */
    public function isSoftDeleteEnabled()
    {
        return $this->softDelete;
    }

    /**
     * Enable soft deletes
     */
    public function enableSoftDelete()
    {
        $this->softDelete = true;
    }

    /**
     * Disable soft deletes
     */
    public function disableSoftDelete()
    {
        $this->softDelete = false;
    }
}