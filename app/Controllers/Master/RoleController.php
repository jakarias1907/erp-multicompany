<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\RolePermissionModel;
use CodeIgniter\HTTP\ResponseInterface;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $rolePermissionModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new RolePermissionModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('roles', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Roles & Permissions',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Roles']
            ]
        ];

        return view('master/role/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('roles', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';

        $builder = $this->roleModel->builder();

        if ($searchValue) {
            $builder->like('name', $searchValue);
        }

        $totalRecords = $this->roleModel->countAll();
        $totalFiltered = $builder->countAllResults(false);

        $roles = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($roles as $role) {
            $isSystem = in_array($role['name'], ['Super Admin', 'Admin']);
            
            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('master/role/permissions/' . $role['id']) . '" class="btn btn-sm btn-warning" title="Manage Permissions">
                        <i class="fas fa-key"></i>
                    </a>
                    <a href="' . base_url('master/role/edit/' . $role['id']) . '" class="btn btn-sm btn-info" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary btn-clone" data-id="' . $role['id'] . '" title="Clone">
                        <i class="fas fa-copy"></i>
                    </button>';
            
            if (!$isSystem) {
                $actions .= '
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $role['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
            }
            
            $actions .= '</div>';

            $data[] = [
                'name' => esc($role['name']),
                'description' => esc($role['description'] ?? '-'),
                'system' => $isSystem ? '<span class="badge badge-info">System Role</span>' : '-',
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('roles', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Role',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Roles', 'url' => base_url('master/role')],
                ['label' => 'Create']
            ]
        ];

        return view('master/role/create', $data);
    }

    public function store()
    {
        if (!hasPermission('roles', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|is_unique[roles.name]',
            'description' => 'permit_empty|max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        $roleId = $this->roleModel->insert($roleData);

        if ($roleId) {
            logActivity('create', 'roles', "Created role: {$roleData['name']}", $roleId);
            return redirect()->to('/master/role')->with('success', 'Role created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create role');
    }

    public function edit($id)
    {
        if (!hasPermission('roles', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('/master/role')->with('error', 'Role not found');
        }

        $data = [
            'title' => 'Edit Role',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Roles', 'url' => base_url('master/role')],
                ['label' => 'Edit']
            ],
            'role' => $role
        ];

        return view('master/role/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('roles', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('/master/role')->with('error', 'Role not found');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => "required|min_length[3]|is_unique[roles.name,id,{$id}]",
            'description' => 'permit_empty|max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $roleData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        if ($this->roleModel->update($id, $roleData)) {
            logActivity('update', 'roles', "Updated role: {$roleData['name']}", $id);
            return redirect()->to('/master/role')->with('success', 'Role updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update role');
    }

    public function delete($id)
    {
        if (!hasPermission('roles', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->response->setJSON(['success' => false, 'message' => 'Role not found']);
        }

        // Check if system role
        if (in_array($role['name'], ['Super Admin', 'Admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete system role']);
        }

        // Check if role is assigned to users
        $db = \Config\Database::connect();
        $userCount = $db->table('company_users')->where('role_id', $id)->countAllResults();
        
        if ($userCount > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete role assigned to users']);
        }

        if ($this->roleModel->delete($id)) {
            logActivity('delete', 'roles', "Deleted role: {$role['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Role deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete role']);
    }

    public function permissions($roleId)
    {
        if (!hasPermission('roles', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $role = $this->roleModel->find($roleId);
        if (!$role) {
            return redirect()->to('/master/role')->with('error', 'Role not found');
        }

        // Get current permissions
        $db = \Config\Database::connect();
        $currentPermissions = $db->table('role_permissions')
            ->select('permissions.module, permissions.action')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->get()
            ->getResultArray();

        $permissionMap = [];
        foreach ($currentPermissions as $perm) {
            if (!isset($permissionMap[$perm['module']])) {
                $permissionMap[$perm['module']] = [];
            }
            $permissionMap[$perm['module']][] = $perm['action'];
        }

        // Define modules and actions
        $modules = [
            'dashboard' => 'Dashboard',
            'companies' => 'Companies',
            'users' => 'Users',
            'roles' => 'Roles & Permissions',
            'products' => 'Products',
            'customers' => 'Customers',
            'suppliers' => 'Suppliers',
            'accounts' => 'Chart of Accounts',
            'journals' => 'Journal Entries',
            'invoices' => 'Invoices',
            'bills' => 'Bills',
            'warehouses' => 'Warehouses',
            'stock' => 'Stock Management',
            'sales' => 'Sales',
            'purchases' => 'Purchases',
            'employees' => 'Employees',
            'reports' => 'Reports'
        ];

        $actions = ['create', 'read', 'update', 'delete', 'approve', 'print', 'export'];

        $data = [
            'title' => 'Manage Permissions',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Roles', 'url' => base_url('master/role')],
                ['label' => 'Permissions']
            ],
            'role' => $role,
            'modules' => $modules,
            'actions' => $actions,
            'permissionMap' => $permissionMap
        ];

        return view('master/role/permissions', $data);
    }

    public function updatePermissions($roleId)
    {
        if (!hasPermission('roles', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $role = $this->roleModel->find($roleId);
        if (!$role) {
            return redirect()->to('/master/role')->with('error', 'Role not found');
        }

        $db = \Config\Database::connect();
        
        // Delete existing permissions
        $db->table('role_permissions')->where('role_id', $roleId)->delete();

        // Insert new permissions
        $permissions = $this->request->getPost('permissions') ?? [];
        
        foreach ($permissions as $permissionKey) {
            list($module, $action) = explode('.', $permissionKey);
            
            // Get or create permission
            $permission = $db->table('permissions')
                ->where('module', $module)
                ->where('action', $action)
                ->get()
                ->getRowArray();
            
            if (!$permission) {
                $permissionId = $db->table('permissions')->insert([
                    'module' => $module,
                    'action' => $action,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $permissionId = $db->insertID();
            } else {
                $permissionId = $permission['id'];
            }
            
            // Insert role permission
            $db->table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);
        }

        logActivity('update', 'roles', "Updated permissions for role: {$role['name']}", $roleId);
        return redirect()->to('/master/role')->with('success', 'Permissions updated successfully');
    }

    public function clone($id)
    {
        if (!hasPermission('roles', 'create')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->response->setJSON(['success' => false, 'message' => 'Role not found']);
        }

        // Create new role
        $newRoleData = [
            'name' => $role['name'] . ' (Copy)',
            'description' => $role['description']
        ];

        $newRoleId = $this->roleModel->insert($newRoleData);

        if ($newRoleId) {
            // Clone permissions
            $db = \Config\Database::connect();
            $permissions = $db->table('role_permissions')
                ->where('role_id', $id)
                ->get()
                ->getResultArray();
            
            foreach ($permissions as $perm) {
                $db->table('role_permissions')->insert([
                    'role_id' => $newRoleId,
                    'permission_id' => $perm['permission_id']
                ]);
            }

            logActivity('create', 'roles', "Cloned role: {$role['name']}", $newRoleId);
            return $this->response->setJSON(['success' => true, 'message' => 'Role cloned successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to clone role']);
    }
}
