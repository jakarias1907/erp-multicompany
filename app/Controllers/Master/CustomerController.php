<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

class CustomerController extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('customers', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Customers',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Customers']
            ]
        ];

        return view('master/customer/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('customers', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->customerModel->builder();
        $builder->where('company_id', $companyId)
                ->where('deleted_at IS NULL');

        if ($searchValue) {
            $builder->groupStart()
                ->like('code', $searchValue)
                ->orLike('name', $searchValue)
                ->orLike('email', $searchValue)
                ->groupEnd();
        }

        $totalFiltered = $builder->countAllResults(false);

        $customers = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($customers as $customer) {
            $typeBadge = $customer['type'] == 'retail' 
                ? '<span class="badge badge-info">Retail</span>'
                : '<span class="badge badge-success">Wholesale</span>';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('master/customer/statement/' . $customer['id']) . '" class="btn btn-sm btn-secondary" title="Statement">
                        <i class="fas fa-file-invoice"></i>
                    </a>
                    <a href="' . base_url('master/customer/edit/' . $customer['id']) . '" class="btn btn-sm btn-info" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $customer['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'code' => esc($customer['code']),
                'name' => esc($customer['name']),
                'type' => $typeBadge,
                'contact_person' => esc($customer['contact_person'] ?? '-'),
                'email' => esc($customer['email'] ?? '-'),
                'phone' => esc($customer['phone'] ?? '-'),
                'credit_limit' => formatCurrency($customer['credit_limit'] ?? 0),
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->customerModel->where('company_id', $companyId)->where('deleted_at IS NULL')->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('customers', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Customer',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Customers', 'url' => base_url('master/customer')],
                ['label' => 'Create']
            ]
        ];

        return view('master/customer/create', $data);
    }

    public function store()
    {
        if (!hasPermission('customers', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'code' => 'required|alpha_numeric|is_unique[customers.code]',
            'name' => 'required|min_length[3]',
            'type' => 'required|in_list[retail,wholesale]',
            'email' => 'permit_empty|valid_email',
            'credit_limit' => 'permit_empty|decimal',
            'payment_term' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $customerData = [
            'company_id' => getCurrentCompanyId(),
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'contact_person' => $this->request->getPost('contact_person'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'credit_limit' => $this->request->getPost('credit_limit') ?? 0,
            'payment_term' => $this->request->getPost('payment_term') ?? 30,
            'created_by' => getCurrentUserId()
        ];

        $customerId = $this->customerModel->insert($customerData);

        if ($customerId) {
            logActivity('create', 'customers', "Created customer: {$customerData['name']}", $customerId);
            return redirect()->to('/master/customer')->with('success', 'Customer created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create customer');
    }

    public function edit($id)
    {
        if (!hasPermission('customers', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $customer = $this->customerModel->where('company_id', $companyId)->find($id);
        
        if (!$customer) {
            return redirect()->to('/master/customer')->with('error', 'Customer not found');
        }

        $data = [
            'title' => 'Edit Customer',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Customers', 'url' => base_url('master/customer')],
                ['label' => 'Edit']
            ],
            'customer' => $customer
        ];

        return view('master/customer/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('customers', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $customer = $this->customerModel->where('company_id', $companyId)->find($id);
        
        if (!$customer) {
            return redirect()->to('/master/customer')->with('error', 'Customer not found');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'code' => "required|alpha_numeric|is_unique[customers.code,id,{$id}]",
            'name' => 'required|min_length[3]',
            'type' => 'required|in_list[retail,wholesale]',
            'email' => 'permit_empty|valid_email',
            'credit_limit' => 'permit_empty|decimal',
            'payment_term' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $customerData = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'contact_person' => $this->request->getPost('contact_person'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'credit_limit' => $this->request->getPost('credit_limit') ?? 0,
            'payment_term' => $this->request->getPost('payment_term') ?? 30,
            'updated_by' => getCurrentUserId()
        ];

        if ($this->customerModel->update($id, $customerData)) {
            logActivity('update', 'customers', "Updated customer: {$customerData['name']}", $id);
            return redirect()->to('/master/customer')->with('success', 'Customer updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update customer');
    }

    public function delete($id)
    {
        if (!hasPermission('customers', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $companyId = getCurrentCompanyId();
        $customer = $this->customerModel->where('company_id', $companyId)->find($id);
        
        if (!$customer) {
            return $this->response->setJSON(['success' => false, 'message' => 'Customer not found']);
        }

        if ($this->customerModel->delete($id)) {
            logActivity('delete', 'customers', "Deleted customer: {$customer['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Customer deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete customer']);
    }

    public function statement($id)
    {
        if (!hasPermission('customers', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $customer = $this->customerModel->where('company_id', $companyId)->find($id);
        
        if (!$customer) {
            return redirect()->to('/master/customer')->with('error', 'Customer not found');
        }

        // Get customer transactions (placeholder for now)
        $data = [
            'title' => 'Customer Statement',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Customers', 'url' => base_url('master/customer')],
                ['label' => 'Statement']
            ],
            'customer' => $customer,
            'transactions' => [] // Will be populated when invoice module is complete
        ];

        return view('master/customer/statement', $data);
    }

    public function getOutstanding($id)
    {
        if (!hasPermission('customers', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $companyId = getCurrentCompanyId();
        $customer = $this->customerModel->where('company_id', $companyId)->find($id);
        
        if (!$customer) {
            return $this->response->setJSON(['error' => 'Customer not found']);
        }

        $outstanding = $this->customerModel->getOutstandingBalance($id);
        
        return $this->response->setJSON([
            'success' => true,
            'outstanding' => $outstanding,
            'credit_limit' => $customer['credit_limit'],
            'available_credit' => $customer['credit_limit'] - $outstanding
        ]);
    }

    public function export()
    {
        if (!hasPermission('customers', 'export')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        // Excel export will be implemented with PhpSpreadsheet
        return redirect()->to('/master/customer')->with('info', 'Export feature coming soon');
    }
}
