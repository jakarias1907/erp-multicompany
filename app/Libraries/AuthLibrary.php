<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Models\LoginAttemptModel;
use App\Models\CompanyUserModel;

class AuthLibrary
{
    protected $session;
    protected $userModel;
    protected $loginAttemptModel;
    protected $companyUserModel;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->userModel = new UserModel();
        $this->loginAttemptModel = new LoginAttemptModel();
        $this->companyUserModel = new CompanyUserModel();
    }
    
    /**
     * Login user
     */
    public function login($username, $password, $remember = false)
    {
        $request = \Config\Services::request();
        $ipAddress = $request->getIPAddress();
        
        // Check for rate limiting
        if ($this->isLocked($username)) {
            return [
                'success' => false,
                'message' => 'Account is locked due to too many failed login attempts. Please try again after 15 minutes.'
            ];
        }
        
        // Find user by username or email
        $user = $this->userModel
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();
        
        if (!$user) {
            $this->recordLoginAttempt($username, $ipAddress, 'failed');
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->recordLoginAttempt($username, $ipAddress, 'failed');
            $this->incrementFailedAttempts($user['id']);
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Your account is not active. Please contact administrator.'
            ];
        }
        
        // Check if account is locked
        if ($user['status'] === 'locked' || ($user['locked_until'] && strtotime($user['locked_until']) > time())) {
            return [
                'success' => false,
                'message' => 'Your account is locked. Please contact administrator.'
            ];
        }
        
        // Get user's companies
        $companyUsers = $this->companyUserModel
            ->select('company_users.*, companies.name as company_name, companies.code as company_code')
            ->join('companies', 'companies.id = company_users.company_id')
            ->where('company_users.user_id', $user['id'])
            ->where('company_users.status', 'active')
            ->findAll();
        
        if (empty($companyUsers)) {
            return [
                'success' => false,
                'message' => 'You are not assigned to any company. Please contact administrator.'
            ];
        }
        
        // Get default company or first company
        $defaultCompany = null;
        foreach ($companyUsers as $cu) {
            if ($cu['is_default'] == 1) {
                $defaultCompany = $cu;
                break;
            }
        }
        
        if (!$defaultCompany) {
            $defaultCompany = $companyUsers[0];
        }
        
        // Set session data
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'current_company_id' => $defaultCompany['company_id'],
            'current_company_name' => $defaultCompany['company_name'],
            'current_company_code' => $defaultCompany['company_code'],
            'role_id' => $defaultCompany['role_id'],
            'force_password_change' => $user['force_password_change'],
            'isLoggedIn' => true,
        ];
        
        $this->session->set($sessionData);
        
        // Update last login
        $this->userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s'),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
        
        // Record successful login
        $this->recordLoginAttempt($username, $ipAddress, 'success');
        
        return [
            'success' => true,
            'message' => 'Login successful.',
            'force_password_change' => $user['force_password_change']
        ];
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $this->session->destroy();
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->session->get('isLoggedIn') === true;
    }
    
    /**
     * Get current user ID
     */
    public function getUserId()
    {
        return $this->session->get('user_id');
    }
    
    /**
     * Get current company ID
     */
    public function getCompanyId()
    {
        return $this->session->get('current_company_id');
    }
    
    /**
     * Get current role ID
     */
    public function getRoleId()
    {
        return $this->session->get('role_id');
    }
    
    /**
     * Switch company
     */
    public function switchCompany($companyId)
    {
        $userId = $this->getUserId();
        
        $companyUser = $this->companyUserModel
            ->select('company_users.*, companies.name as company_name, companies.code as company_code')
            ->join('companies', 'companies.id = company_users.company_id')
            ->where('company_users.user_id', $userId)
            ->where('company_users.company_id', $companyId)
            ->where('company_users.status', 'active')
            ->first();
        
        if (!$companyUser) {
            return false;
        }
        
        $this->session->set([
            'current_company_id' => $companyUser['company_id'],
            'current_company_name' => $companyUser['company_name'],
            'current_company_code' => $companyUser['company_code'],
            'role_id' => $companyUser['role_id'],
        ]);
        
        return true;
    }
    
    /**
     * Check if account is locked
     */
    protected function isLocked($username)
    {
        $attempts = $this->loginAttemptModel
            ->where('username', $username)
            ->where('status', 'failed')
            ->where('created_at >', date('Y-m-d H:i:s', strtotime('-15 minutes')))
            ->countAllResults();
        
        return $attempts >= 5;
    }
    
    /**
     * Record login attempt
     */
    protected function recordLoginAttempt($username, $ipAddress, $status)
    {
        $this->loginAttemptModel->insert([
            'username' => $username,
            'ip_address' => $ipAddress,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Increment failed login attempts
     */
    protected function incrementFailedAttempts($userId)
    {
        $user = $this->userModel->find($userId);
        $attempts = $user['failed_login_attempts'] + 1;
        
        $data = ['failed_login_attempts' => $attempts];
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $data['locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $data['status'] = 'locked';
        }
        
        $this->userModel->update($userId, $data);
    }
}
