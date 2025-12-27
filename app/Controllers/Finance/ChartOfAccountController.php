<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\ChartOfAccountModel;

class ChartOfAccountController extends BaseController
{
    protected $accountModel;

    public function __construct()
    {
        $this->accountModel = new ChartOfAccountModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('accounts', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Chart of Accounts',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Chart of Accounts']
            ]
        ];

        return view('finance/account/index', $data);
    }

    public function getTreeData()
    {
        if (!hasPermission('accounts', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $companyId = getCurrentCompanyId();
        
        $accounts = $this->accountModel
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->findAll();

        $tree = $this->buildTree($accounts);
        return $this->response->setJSON($tree);
    }

    private function buildTree($accounts, $parentId = null)
    {
        $branch = [];

        foreach ($accounts as $account) {
            if ($account['parent_id'] == $parentId) {
                $children = $this->buildTree($accounts, $account['id']);
                
                $node = [
                    'id' => $account['id'],
                    'text' => $account['code'] . ' - ' . $account['name'],
                    'type' => $account['account_type'],
                    'data' => $account
                ];

                if ($children) {
                    $node['children'] = $children;
                }

                $branch[] = $node;
            }
        }

        return $branch;
    }

    public function create()
    {
        if (!hasPermission('accounts', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $parentAccounts = $this->accountModel
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Create Account',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Chart of Accounts', 'url' => base_url('finance/account')],
                ['label' => 'Create']
            ],
            'parentAccounts' => $parentAccounts
        ];

        return view('finance/account/create', $data);
    }

    public function store()
    {
        if (!hasPermission('accounts', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'code' => 'required|max_length[20]',
            'name' => 'required|max_length[255]',
            'account_type' => 'required|in_list[asset,liability,equity,revenue,expense]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'account_type' => $this->request->getPost('account_type'),
            'description' => $this->request->getPost('description'),
            'is_active' => 1,
            'created_by' => getCurrentUserId()
        ];

        if ($this->accountModel->insert($data)) {
            logActivity('create', 'accounts', "Created account: {$data['code']} - {$data['name']}", $this->accountModel->getInsertID());
            return redirect()->to('/finance/account')->with('success', 'Account created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create account');
    }

    public function edit($id)
    {
        if (!hasPermission('accounts', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $account = $this->accountModel->find($id);
        if (!$account) {
            return redirect()->to('/finance/account')->with('error', 'Account not found');
        }

        $companyId = getCurrentCompanyId();
        $parentAccounts = $this->accountModel
            ->where('company_id', $companyId)
            ->where('id !=', $id)
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Edit Account',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Chart of Accounts', 'url' => base_url('finance/account')],
                ['label' => 'Edit']
            ],
            'account' => $account,
            'parentAccounts' => $parentAccounts
        ];

        return view('finance/account/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('accounts', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $account = $this->accountModel->find($id);
        if (!$account) {
            return redirect()->to('/finance/account')->with('error', 'Account not found');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'code' => 'required|max_length[20]',
            'name' => 'required|max_length[255]',
            'account_type' => 'required|in_list[asset,liability,equity,revenue,expense]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'account_type' => $this->request->getPost('account_type'),
            'description' => $this->request->getPost('description'),
            'updated_by' => getCurrentUserId()
        ];

        if ($this->accountModel->update($id, $data)) {
            logActivity('update', 'accounts', "Updated account: {$data['code']} - {$data['name']}", $id);
            return redirect()->to('/finance/account')->with('success', 'Account updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update account');
    }

    public function delete($id)
    {
        if (!hasPermission('accounts', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $account = $this->accountModel->find($id);
        if (!$account) {
            return $this->response->setJSON(['success' => false, 'message' => 'Account not found']);
        }

        // Check if account is used in journal entries
        $db = \Config\Database::connect();
        $usageCount = $db->table('journal_entry_lines')->where('account_id', $id)->countAllResults();
        
        if ($usageCount > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => "Cannot delete account. It is used in {$usageCount} journal entry line(s)"
            ]);
        }

        // Check if account has children
        $childrenCount = $this->accountModel->where('parent_id', $id)->countAllResults();
        if ($childrenCount > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => "Cannot delete account. It has {$childrenCount} child account(s)"
            ]);
        }

        if ($this->accountModel->delete($id)) {
            logActivity('delete', 'accounts', "Deleted account: {$account['code']} - {$account['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Account deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete account']);
    }

    public function toggleStatus($id)
    {
        if (!hasPermission('accounts', 'update')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $account = $this->accountModel->find($id);
        if (!$account) {
            return $this->response->setJSON(['success' => false, 'message' => 'Account not found']);
        }

        $newStatus = $account['is_active'] ? 0 : 1;
        
        if ($this->accountModel->update($id, ['is_active' => $newStatus])) {
            $status = $newStatus ? 'activated' : 'deactivated';
            logActivity('update', 'accounts', "Account {$status}: {$account['code']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => "Account {$status} successfully"]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to toggle status']);
    }
}
