<?php
namespace App\Models;

/**
 * JWT Token Model
 * مدیریت توکن‌های JWT
 */
class JwtToken extends Model
{
    protected $table = 'jwt_tokens';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'token',
        'refresh_token',
        'expires_at',
        'is_revoked',
    ];
    
    protected $casts = [
        'is_revoked' => 'bool',
    ];
    
    /**
     * Create new token
     */
    public function createToken($userId, $token, $refreshToken = null, $expiryHours = 24)
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
        
        return $this->db->insert('jwt_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'is_revoked' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Create remember me token
     */
    public function createRememberToken($userId, $token, $days = 30)
    {
        $refreshToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        
        return $this->db->insert('jwt_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'is_revoked' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Validate token
     */
    public function validateToken($token)
    {
        $sql = "SELECT * FROM jwt_tokens 
                WHERE token = ? AND expires_at > NOW() AND is_revoked = 0";
        
        return $this->db->selectOne($sql, [$token]);
    }
    
    /**
     * Revoke token
     */
    public function revokeToken($token)
    {
        return $this->db->update('jwt_tokens', [
            'is_revoked' => 1
        ], 'token = ?', [$token]);
    }
    
    /**
     * Revoke all tokens for user
     */
    public function revokeAllForUser($userId)
    {
        return $this->db->update('jwt_tokens', [
            'is_revoked' => 1
        ], 'user_id = ? AND is_revoked = 0', [$userId]);
    }
    
    /**
     * Clean expired tokens
     */
    public function cleanExpired()
    {
        return $this->db->delete('jwt_tokens', 'expires_at < NOW()');
    }
    
    /**
     * Get user tokens
     */
    public function getUserTokens($userId)
    {
        $sql = "SELECT * FROM jwt_tokens WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * Get active token count for user
     */
    public function getActiveCount($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM jwt_tokens 
                WHERE user_id = ? AND expires_at > NOW() AND is_revoked = 0";
        $result = $this->db->selectOne($sql, [$userId]);
        return (int)($result['total'] ?? 0);
    }
}