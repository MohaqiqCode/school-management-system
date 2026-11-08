<?php
namespace App\Traits;

use App\Core\Application;

/**
 * HasAuditLog Trait
 * 
 * تریت ثبت لاگ‌های ممیزی
 * قابل استفاده در Model ها و Controller ها
 * 
 * Usage:
 *   class Student extends Model {
 *       use HasAuditLog;
 *   }
 * 
 *   $student->logCreate('دانش‌آموز جدید ایجاد شد');
 *   $student->logUpdate('اطلاعات دانش‌آموز ویرایش شد');
 *   $student->logDelete('دانش‌آموز حذف شد');
 *   $student->logCustom('export', 'گزارش دانش‌آموزان دریافت شد');
 */
trait HasAuditLog
{
    /**
     * @var bool Enable automatic audit logging
     */
    protected $auditEnabled = true;

    /**
     * @var string Module name for audit logs
     */
    protected $auditModule = '';

    /**
     * @var array Fields to exclude from change tracking
     */
    protected $auditExclude = ['password', 'remember_token', 'updated_at'];

    /**
     * @var array Original attributes before changes
     */
    protected $originalAuditAttributes = [];

    /**
     * Boot the trait
     */
    protected function bootHasAuditLog()
    {
        // Store original attributes when model is loaded
        if ($this->auditEnabled) {
            $this->originalAuditAttributes = $this->attributes ?? [];
        }
    }

    /**
     * Log a create action
     * 
     * @param string $description Optional description
     * @param array $metadata Additional metadata
     * @return int|false Log ID
     */
    public function logCreate($description = null, $metadata = [])
    {
        if (!$this->auditEnabled) return false;

        $module = $this->getAuditModule();
        $description = $description ?? "{$module} created";

        return $this->writeAuditLog('create', $module, $description, [], $this->getAuditAttributes(), $metadata);
    }

    /**
     * Log an update action
     * 
     * @param string $description Optional description
     * @param array $metadata Additional metadata
     * @return int|false Log ID
     */
    public function logUpdate($description = null, $metadata = [])
    {
        if (!$this->auditEnabled) return false;

        $module = $this->getAuditModule();
        $description = $description ?? "{$module} updated";

        $oldValues = $this->originalAuditAttributes;
        $newValues = $this->getAuditAttributes();

        // Only log if there are actual changes
        $changes = $this->getChangedAttributes($oldValues, $newValues);
        if (empty($changes)) return false;

        return $this->writeAuditLog('update', $module, $description, $changes['old'], $changes['new'], $metadata);
    }

    /**
     * Log a delete action
     * 
     * @param string $description Optional description
     * @param array $metadata Additional metadata
     * @return int|false Log ID
     */
    public function logDelete($description = null, $metadata = [])
    {
        if (!$this->auditEnabled) return false;

        $module = $this->getAuditModule();
        $description = $description ?? "{$module} deleted";

        return $this->writeAuditLog('delete', $module, $description, $this->getAuditAttributes(), [], $metadata);
    }

    /**
     * Log a custom action
     * 
     * @param string $action Action name
     * @param string $description Description
     * @param array $metadata Additional metadata
     * @return int|false Log ID
     */
    public function logCustom($action, $description, $metadata = [])
    {
        if (!$this->auditEnabled) return false;

        $module = $this->getAuditModule();

        return $this->writeAuditLog($action, $module, $description, [], [], $metadata);
    }

    /**
     * Log a login action
     * 
     * @param string $description Optional description
     * @return int|false Log ID
     */
    public function logLogin($description = null)
    {
        return $this->logCustom('login', $description ?? 'User logged in');
    }

    /**
     * Log a logout action
     * 
     * @param string $description Optional description
     * @return int|false Log ID
     */
    public function logLogout($description = null)
    {
        return $this->logCustom('logout', $description ?? 'User logged out');
    }

    /**
     * Log a login failed action
     * 
     * @param string $reason Failure reason
     * @return int|false Log ID
     */
    public function logLoginFailed($reason = '')
    {
        return $this->logCustom('login_failed', 'Login failed' . ($reason ? ": {$reason}" : ''));
    }

    /**
     * Log a password change
     * 
     * @return int|false Log ID
     */
    public function logPasswordChange()
    {
        return $this->logCustom('password_reset', 'Password changed');
    }

    /**
     * Log a password reset request
     * 
     * @return int|false Log ID
     */
    public function logPasswordResetRequest()
    {
        return $this->logCustom('password_reset_request', 'Password reset requested');
    }

    /**
     * Log an export action
     * 
     * @param string $type Export type (excel, pdf, csv)
     * @param string $description Optional description
     * @return int|false Log ID
     */
    public function logExport($type, $description = null)
    {
        return $this->logCustom('export', $description ?? "Data exported as {$type}");
    }

    /**
     * Log an import action
     * 
     * @param string $type Import type
     * @param int $count Number of records imported
     * @return int|false Log ID
     */
    public function logImport($type, $count = 0)
    {
        return $this->logCustom('import', "Imported {$count} {$type} records");
    }

    /**
     * Log a status change
     * 
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @return int|false Log ID
     */
    public function logStatusChange($oldStatus, $newStatus)
    {
        return $this->logCustom('status_change', "Status changed from '{$oldStatus}' to '{$newStatus}'", [
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus]
        ]);
    }

    /**
     * Get audit logs for this model
     * 
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function getAuditLogs($limit = 50)
    {
        $modelId = $this->getAuditModelId();
        $module = $this->getAuditModule();

        if (!$modelId) return [];

        $db = Application::getInstance()->getDB();

        return $db->select(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM audit_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.module = ? 
             AND (al.description LIKE ? OR al.description LIKE ?)
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$module, "%ID:{$modelId}%", "%#{$modelId}%", $limit]
        );
    }

    /**
     * Get the current user ID
     * 
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        $session = Application::getInstance()->getSession();
        return $session->get('user_id');
    }

    /**
     * Get the current IP address
     * 
     * @return string
     */
    protected function getCurrentIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Get the current user agent
     * 
     * @return string
     */
    protected function getCurrentUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Get the audit module name
     * 
     * @return string
     */
    protected function getAuditModule()
    {
        if (!empty($this->auditModule)) {
            return $this->auditModule;
        }

        // Auto-detect from class name
        $class = get_class($this);
        $parts = explode('\\', $class);
        $className = end($parts);

        // Convert CamelCase to snake_case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Get the model ID for audit reference
     * 
     * @return int|string|null
     */
    protected function getAuditModelId()
    {
        return $this->attributes['id'] ?? null;
    }

    /**
     * Get attributes for audit (excluding sensitive fields)
     * 
     * @return array
     */
    protected function getAuditAttributes()
    {
        $attributes = $this->attributes ?? [];

        // Remove excluded fields
        foreach ($this->auditExclude as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Get changed attributes between old and new values
     * 
     * @param array $oldValues Old values
     * @param array $newValues New values
     * @return array ['old' => [...], 'new' => [...]]
     */
    protected function getChangedAttributes($oldValues, $newValues)
    {
        $changes = ['old' => [], 'new' => []];

        foreach ($newValues as $key => $value) {
            $oldValue = $oldValues[$key] ?? null;

            // Skip if values are the same
            if ($oldValue === $value) continue;

            // Skip excluded fields
            if (in_array($key, $this->auditExclude)) continue;

            // Skip timestamp-only changes
            if ($key === 'updated_at' && count(array_diff_assoc($newValues, $oldValues)) === 1) {
                continue;
            }

            $changes['old'][$key] = $oldValue;
            $changes['new'][$key] = $value;
        }

        return $changes;
    }

    /**
     * Write audit log entry
     * 
     * @param string $action Action name
     * @param string $module Module name
     * @param string $description Description
     * @param array $oldValues Old values
     * @param array $newValues New values
     * @param array $metadata Additional metadata
     * @return int|false Log ID
     */
    protected function writeAuditLog($action, $module, $description, $oldValues = [], $newValues = [], $metadata = [])
    {
        try {
            $db = Application::getInstance()->getDB();

            // Merge metadata old/new values
            if (isset($metadata['old_values'])) {
                $oldValues = array_merge($oldValues, $metadata['old_values']);
                unset($metadata['old_values']);
            }
            if (isset($metadata['new_values'])) {
                $newValues = array_merge($newValues, $metadata['new_values']);
                unset($metadata['new_values']);
            }

            // Add model ID to description if available
            $modelId = $this->getAuditModelId();
            if ($modelId) {
                $description = $description . " (ID: {$modelId})";
            }

            $logData = [
                'user_id' => $this->getCurrentUserId(),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => !empty($oldValues) ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => !empty($newValues) ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $this->getCurrentIp(),
                'user_agent' => $this->getCurrentUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $db->insert('audit_logs', $logData);

        } catch (\Exception $e) {
            // Silently fail - audit logging should not break the application
            error_log('Audit log write failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable audit logging
     */
    public function enableAudit()
    {
        $this->auditEnabled = true;
    }

    /**
     * Disable audit logging
     */
    public function disableAudit()
    {
        $this->auditEnabled = false;
    }

    /**
     * Set audit module name
     * 
     * @param string $module
     */
    public function setAuditModule($module)
    {
        $this->auditModule = $module;
    }

    /**
     * Add fields to exclude from audit
     * 
     * @param array $fields
     */
    public function addAuditExclude(array $fields)
    {
        $this->auditExclude = array_merge($this->auditExclude, $fields);
    }
}