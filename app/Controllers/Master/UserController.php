<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\CompanyUserModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{
    protected $userModel;
    protected $companyUserModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->companyUserModel = new CompanyUserModel();
        $this->roleModel = new RoleModel();
        helper(['form', 'url']);
    }

    /**
     * List all users
     */
    public function index()
    {
        if (!hasPermission('users', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Users',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Users']
            ]
        ];

        return view('master/user/index', $data);
    }

    /**
     * DataTables server-side processing
     */
    public function datatable()
    {
        if (!hasPermission('users', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';

        // Base query
        $builder = $this->userModel->builder();
        $builder->where('deleted_at IS NULL');

        // Search
        if ($searchValue) {
            $builder->groupStart()
                ->like('username', $searchValue)
                ->orLike('email', $searchValue)
                ->orLike('full_name', $searchValue)
                ->groupEnd();
        }

        // Total records
        $totalRecords = $this->userModel->where('deleted_at IS NULL')->countAllResults(false);
        $totalFiltered = $builder->countAllResults(false);

        // Get data
        $users = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // Format data for DataTables
        $data = [];
        foreach ($users as $user) {
            $statusBadge = $user['status'] == 'active' 
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">' . ucfirst($user['status']) . '</span>';

            $photo = $user['photo'] 
                ? '<img src="' . base_url('uploads/users/' . $user['photo']) . '" width="40" class="img-thumbnail">'
                : '<span class="badge badge-secondary">No Photo</span>';

            $lastLogin = $user['last_login'] 
                ? formatDate($user['last_login'], 'Y-m-d H:i:s')
                : '-';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('master/user/edit/' . $user['id']) . '" class="btn btn-sm btn-info" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-warning btn-reset-password" data-id="' . $user['id'] . '" title="Reset Password">
                        <i class="fas fa-key"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-' . ($user['status'] == 'active' ? 'secondary' : 'success') . ' btn-toggle-status" data-id="' . $user['id'] . '" data-status="' . $user['status'] . '" title="Toggle Status">
                        <i class="fas fa-' . ($user['status'] == 'active' ? 'ban' : 'check') . '"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $user['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'username' => esc($user['username']),
                'photo' => $photo,
                'full_name' => esc($user['full_name']),
                'email' => esc($user['email']),
                'phone' => esc($user['phone'] ?? '-'),
                'last_login' => $lastLogin,
                'status' => $statusBadge,
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

    /**
     * Show create form
     */
    public function create()
    {
        if (!hasPermission('users', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create User',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Users', 'url' => base_url('master/user')],
                ['label' => 'Create']
            ],
            'roles' => $this->roleModel->findAll()
        ];

        return view('master/user/create', $data);
    }

    /**
     * Store new user
     */
    public function store()
    {
        if (!hasPermission('users', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|alpha_numeric|min_length[3]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[3]',
            'phone' => 'permit_empty|numeric|max_length[20]',
            'photo' => 'permit_empty|max_size[photo,2048]|is_image[photo]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'status' => 'active'
        ];

        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/users', $newName);
            $userData['photo'] = $newName;
        }

        $userId = $this->userModel->insert($userData);

        if ($userId) {
            logActivity('create', 'users', "Created user: {$userData['username']}", $userId);
            return redirect()->to('/master/user')->with('success', 'User created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create user');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        if (!hasPermission('users', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/master/user')->with('error', 'User not found');
        }

        $data = [
            'title' => 'Edit User',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Users', 'url' => base_url('master/user')],
                ['label' => 'Edit']
            ],
            'user' => $user,
            'roles' => $this->roleModel->findAll()
        ];

        return view('master/user/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        if (!hasPermission('users', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/master/user')->with('error', 'User not found');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => "required|alpha_numeric|min_length[3]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'password' => 'permit_empty|min_length[6]',
            'full_name' => 'required|min_length[3]',
            'phone' => 'permit_empty|numeric|max_length[20]',
            'photo' => 'permit_empty|max_size[photo,2048]|is_image[photo]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone')
        ];

        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            // Delete old photo
            if ($user['photo'] && file_exists(FCPATH . 'uploads/users/' . $user['photo'])) {
                unlink(FCPATH . 'uploads/users/' . $user['photo']);
            }
            
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/users', $newName);
            $userData['photo'] = $newName;
        }

        if ($this->userModel->update($id, $userData)) {
            logActivity('update', 'users', "Updated user: {$userData['username']}", $id);
            return redirect()->to('/master/user')->with('success', 'User updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update user');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        if (!hasPermission('users', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        if ($this->userModel->delete($id)) {
            logActivity('delete', 'users', "Deleted user: {$user['username']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete user']);
    }

    /**
     * Reset password
     */
    public function resetPassword($id)
    {
        if (!hasPermission('users', 'update')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        // Reset to default password
        $defaultPassword = 'password123';
        $userData = [
            'password' => password_hash($defaultPassword, PASSWORD_DEFAULT),
            'force_password_change' => 1
        ];

        if ($this->userModel->update($id, $userData)) {
            logActivity('update', 'users', "Reset password for user: {$user['username']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Password reset to: ' . $defaultPassword]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to reset password']);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        if (!hasPermission('users', 'update')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        $newStatus = $user['status'] == 'active' ? 'inactive' : 'active';
        
        if ($this->userModel->update($id, ['status' => $newStatus])) {
            logActivity('update', 'users', "Changed status to {$newStatus} for user: {$user['username']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Status updated successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update status']);
    }

    /**
     * Upload user photo via AJAX
     */
    public function uploadPhoto()
    {
        if (!hasPermission('users', 'update')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/users', $newName);
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Photo uploaded successfully',
                'file' => $newName
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Invalid file']);
    }
}
