<?php

if (!function_exists('hasPermission')) {
    /**
     * Check if current user has permission
     * 
     * @param string $module Module name (e.g., 'products', 'invoices')
     * @param string $action Action name (e.g., 'create', 'edit', 'delete')
     * @return bool
     */
    function hasPermission(string $module, string $action): bool
    {
        // For now, return true. Will be implemented with actual permission checking
        // TODO: Implement actual RBAC permission checking from database
        return true;
    }
}

if (!function_exists('getCurrentCompanyId')) {
    /**
     * Get current company ID from session
     * 
     * @return int|null
     */
    function getCurrentCompanyId(): ?int
    {
        $companyId = session()->get('current_company_id');
        return $companyId ? (int) $companyId : null;
    }
}

if (!function_exists('getCurrentUserId')) {
    /**
     * Get current user ID from session
     * 
     * @return int|null
     */
    function getCurrentUserId(): ?int
    {
        $userId = session()->get('user_id');
        return $userId ? (int) $userId : null;
    }
}

if (!function_exists('logActivity')) {
    /**
     * Log user activity
     * 
     * @param string $action Action performed (create, update, delete, view)
     * @param string $module Module name
     * @param string $description Description of the action
     * @param int|null $recordId Related record ID
     * @return void
     */
    function logActivity(string $action, string $module, string $description, ?int $recordId = null): void
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_activity_logs');
        
        $data = [
            'user_id' => getCurrentUserId(),
            'company_id' => getCurrentCompanyId(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'record_id' => $recordId,
            'ip_address' => \Config\Services::request()->getIPAddress(),
            'user_agent' => \Config\Services::request()->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $builder->insert($data);
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format number as currency
     * 
     * @param float $amount
     * @param string $currency
     * @return string
     */
    function formatCurrency(float $amount, string $currency = 'IDR'): string
    {
        return $currency . ' ' . number_format($amount, 2, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date
     * 
     * @param string|null $date
     * @param string $format
     * @return string
     */
    function formatDate(?string $date, string $format = 'Y-m-d'): string
    {
        if (!$date) {
            return '-';
        }
        return date($format, strtotime($date));
    }
}
