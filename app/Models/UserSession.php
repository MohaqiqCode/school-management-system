<?php
namespace App\Models;

/**
 * User Session Model
 * مدیریت نشست‌های کاربران
 */
class UserSession extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
        'expires_at',
    ];
    
    protected $casts = [
        'last_activity' => 'int',
        'payload' => 'json',
    ];
    
    /**
     * Create new session
     */
    public function createSession($userId, $sessionId, $ipAddress, $userAgent)
    {
        return $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity' => time(),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get active sessions for user
     */
    public function getActiveSessions($userId)
    {
        $sql = "SELECT * FROM user_sessions 
                WHERE user_id = ? AND expires_at > NOW()
                ORDER BY last_activity DESC";
        
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * Count active sessions for user
     */
    public function countActiveSessions($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM user_sessions 
                WHERE user_id = ? AND expires_at > NOW()";
        $result = $this->db->selectOne($sql, [$userId]);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Delete session by ID
     */
    public function deleteBySessionId($sessionId)
    {
        return $this->db->delete('user_sessions', 'session_id = ?', [$sessionId]);
    }
    
    /**
     * Delete all sessions for user (force logout)
     */
    public function deleteAllForUser($userId)
    {
        return $this->db->delete('user_sessions', 'user_id = ?', [$userId]);
    }
    
    /**
     * Delete all sessions except current
     */
    public function deleteAllExcept($userId, $currentSessionId)
    {
        return $this->db->delete(
            'user_sessions', 
            'user_id = ? AND session_id != ?', 
            [$userId, $currentSessionId]
        );
    }
    
    /**
     * Update session activity
     */
    public function updateActivity($sessionId)
    {
        return $this->db->update('user_sessions', [
            'last_activity' => time(),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours'))
        ], 'session_id = ?', [$sessionId]);
    }
    
    /**
     * Clean expired sessions
     */
    public function cleanExpired()
    {
        return $this->db->delete('user_sessions', 'expires_at < NOW()');
    }
    
    /**
     * Get total active sessions count
     */
    public function getTotalActive()
    {
        $sql = "SELECT COUNT(*) as total FROM user_sessions WHERE expires_at > NOW()";
        $result = $this->db->selectOne($sql);
        return (int)($result['total'] ?? 0);
    }
}