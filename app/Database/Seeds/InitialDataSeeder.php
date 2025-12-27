<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Create default company
        $companyData = [
            'name' => 'Demo Company',
            'code' => 'DEMO',
            'address' => 'Jakarta, Indonesia',
            'phone' => '+62 21 12345678',
            'email' => 'demo@erp.com',
            'tax_id' => '01.234.567.8-901.000',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->table('companies')->insert($companyData);
        $companyId = $this->db->insertID();
        
        // Create default super admin user
        $userData = [
            'username' => 'admin',
            'email' => 'admin@erp.com',
            'password' => password_hash('Admin@123456', PASSWORD_BCRYPT),
            'full_name' => 'Super Administrator',
            'phone' => '+62 812 3456 7890',
            'status' => 'active',
            'force_password_change' => 0,
            'two_factor_enabled' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->table('users')->insert($userData);
        $userId = $this->db->insertID();
        
        // Create system roles
        $roles = [
            ['company_id' => null, 'name' => 'Super Admin', 'description' => 'Full system access', 'is_system_role' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'name' => 'Company Admin', 'description' => 'Full company access', 'is_system_role' => 0, 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'name' => 'Manager', 'description' => 'Manager level access', 'is_system_role' => 0, 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'name' => 'Staff', 'description' => 'Staff level access', 'is_system_role' => 0, 'created_at' => date('Y-m-d H:i:s')],
        ];
        
        $this->db->table('roles')->insertBatch($roles);
        $superAdminRoleId = $this->db->insertID();
        
        // Create default permissions
        $modules = ['dashboard', 'companies', 'users', 'roles', 'products', 'customers', 'suppliers', 
                    'invoices', 'bills', 'warehouses', 'inventory', 'sales', 'purchase', 'reports', 'settings'];
        $actions = ['create', 'read', 'update', 'delete', 'approve', 'print', 'export'];
        
        $permissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = [
                    'module' => $module,
                    'action' => $action,
                    'description' => ucfirst($action) . ' ' . $module,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        
        $this->db->table('permissions')->insertBatch($permissions);
        
        // Assign all permissions to Super Admin role
        $allPermissions = $this->db->table('permissions')->get()->getResultArray();
        $rolePermissions = [];
        foreach ($allPermissions as $permission) {
            $rolePermissions[] = [
                'role_id' => $superAdminRoleId,
                'permission_id' => $permission['id'],
            ];
        }
        
        $this->db->table('role_permissions')->insertBatch($rolePermissions);
        
        // Assign user to company with Super Admin role
        $companyUserData = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'role_id' => $superAdminRoleId,
            'is_default' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->table('company_users')->insert($companyUserData);
        
        // Create default warehouse
        $warehouseData = [
            'company_id' => $companyId,
            'name' => 'Main Warehouse',
            'code' => 'WH001',
            'address' => 'Jakarta, Indonesia',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $this->db->table('warehouses')->insert($warehouseData);
        
        // Create default units
        $units = [
            ['company_id' => $companyId, 'name' => 'Piece', 'code' => 'PCS', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'name' => 'Box', 'code' => 'BOX', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'name' => 'Kilogram', 'code' => 'KG', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'name' => 'Meter', 'code' => 'M', 'created_at' => date('Y-m-d H:i:s')],
        ];
        
        $this->db->table('units')->insertBatch($units);
        
        // Create default chart of accounts
        $accounts = [
            // Assets
            ['company_id' => $companyId, 'code' => '1000', 'name' => 'ASSETS', 'account_type' => 'assets', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '1100', 'name' => 'Cash and Bank', 'account_type' => 'assets', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '1200', 'name' => 'Accounts Receivable', 'account_type' => 'assets', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '1300', 'name' => 'Inventory', 'account_type' => 'assets', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            // Liabilities
            ['company_id' => $companyId, 'code' => '2000', 'name' => 'LIABILITIES', 'account_type' => 'liabilities', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '2100', 'name' => 'Accounts Payable', 'account_type' => 'liabilities', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            // Equity
            ['company_id' => $companyId, 'code' => '3000', 'name' => 'EQUITY', 'account_type' => 'equity', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '3100', 'name' => 'Capital', 'account_type' => 'equity', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            // Revenue
            ['company_id' => $companyId, 'code' => '4000', 'name' => 'REVENUE', 'account_type' => 'revenue', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '4100', 'name' => 'Sales Revenue', 'account_type' => 'revenue', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            // Expenses
            ['company_id' => $companyId, 'code' => '5000', 'name' => 'EXPENSES', 'account_type' => 'expenses', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '5100', 'name' => 'Cost of Goods Sold', 'account_type' => 'expenses', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['company_id' => $companyId, 'code' => '5200', 'name' => 'Operating Expenses', 'account_type' => 'expenses', 'parent_id' => null, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
        ];
        
        $this->db->table('chart_of_accounts')->insertBatch($accounts);
        
        echo "Initial data seeded successfully!\n";
        echo "Login credentials:\n";
        echo "Email: admin@erp.com\n";
        echo "Password: Admin@123456\n";
    }
}
