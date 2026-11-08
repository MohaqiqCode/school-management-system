<?php
namespace App\Middleware;

/**
 * Guest Middleware
 * فقط برای کاربرانی که وارد نشده‌اند
 */
class GuestMiddleware extends Middleware
{
    /**
     * Handle guest check
     * 
     * @return bool
     */
    public function handle()
    {
        // If user is already logged in, redirect to dashboard
        if ($this->session->has('user_id')) {
            $user = $this->getCurrentUser();
            
            if ($user && $user['is_active']) {
                if ($this->request->wantsJson()) {
                    $this->response->json([
                        'success' => false,
                        'message' => 'شما قبلاً وارد سیستم شده‌اید'
                    ], 403);
                } else {
                    $this->response->redirect('/dashboard');
                }
                return false;
            }
        }
        
        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            if ($this->request->wantsJson()) {
                $this->response->json([
                    'success' => false,
                    'message' => 'شما قبلاً وارد سیستم شده‌اید'
                ], 403);
            } else {
                $this->response->redirect('/dashboard');
            }
            return false;
        }
        
        return true;
    }
}