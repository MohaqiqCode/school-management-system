<?php
namespace App\Middleware;

/**
 * Branch Access Middleware
 * محدود کردن دسترسی به داده‌های شعبه
 */
class BranchAccessMiddleware extends Middleware
{
    /**
     * Handle branch access check
     * 
     * @return bool
     */
    public function handle()
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return true; // Let Auth middleware handle this
        }
        
        // Super admin can access all branches
        if ($user['role_name'] === 'super_admin') {
            return true;
        }
        
        // Branch-specific roles
        $branchRestrictedRoles = [
            'branch_admin',
            'principal',
            'academic_coordinator',
            'accountant',
            'teacher',
            'librarian',
            'transport_manager',
            'employee',
        ];
        
        if (in_array($user['role_name'], $branchRestrictedRoles)) {
            $userBranchId = $user['branch_id'];
            
            // Check if user is trying to access another branch's data
            $requestedBranchId = $this->getRequestedBranchId();
            
            if ($requestedBranchId && $requestedBranchId != $userBranchId) {
                $this->log('branch_access_denied',
                    "User {$user['id']} from branch {$userBranchId} attempted to access branch {$requestedBranchId}"
                );
                
                if ($this->request->wantsJson()) {
                    $this->response->json([
                        'success' => false,
                        'message' => 'شما فقط به داده‌های شعبه خود دسترسی دارید'
                    ], 403);
                } else {
                    $this->session->flash('error', 'شما فقط به داده‌های شعبه خود دسترسی دارید');
                    $this->response->redirect('/dashboard');
                }
                
                return false;
            }
            
            // If no branch specified, set user's branch automatically
            if (!$requestedBranchId) {
                $_GET['branch_id'] = $userBranchId;
                $_POST['branch_id'] = $userBranchId;
            }
        }
        
        return true;
    }
    
    /**
     * Get requested branch ID from request
     * 
     * @return int|null
     */
    private function getRequestedBranchId()
    {
        // Check URL segments
        $uri = $this->request->getUri();
        if (preg_match('/\/branches\/(\d+)/', $uri, $matches)) {
            return (int) $matches[1];
        }
        
        // Check query parameters
        $branchId = $this->request->get('branch_id');
        if ($branchId) {
            return (int) $branchId;
        }
        
        // Check POST data
        $branchId = $this->request->post('branch_id');
        if ($branchId) {
            return (int) $branchId;
        }
        
        return null;
    }
}