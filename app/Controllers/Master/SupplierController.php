<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\SupplierModel;

class SupplierController extends BaseController
{
    protected $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('suppliers', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Suppliers',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Suppliers']
            ]
        ];

        return view('master/supplier/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('suppliers', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->supplierModel->builder();
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

        $suppliers = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($suppliers as $supplier) {
            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('master/supplier/statement/' . $supplier['id']) . '" class="btn btn-sm btn-secondary" title="Statement">
                        <i class="fas fa-file-invoice"></i>
                    </a>
                    <a href="' . base_url('master/supplier/edit/' . $supplier['id']) . '" class="btn btn-sm btn-info" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $supplier['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'code' => esc($supplier['code']),
                'name' => esc($supplier['name']),
                'contact_person' => esc($supplier['contact_person'] ?? '-'),
                'email' => esc($supplier['email'] ?? '-'),
                'phone' => esc($supplier['phone'] ?? '-'),
                'payment_term' => $supplier['payment_term'] . ' days',
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->supplierModel->where('company_id', $companyId)->where('deleted_at IS NULL')->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('suppliers', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Supplier',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Suppliers', 'url' => base_url('master/supplier')],
                ['label' => 'Create']
            ]
        ];

        return view('master/supplier/create', $data);
    }

    public function store()
    {
        if (!hasPermission('suppliers', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'code' => 'required|alpha_numeric|is_unique[suppliers.code]',
            'name' => 'required|min_length[3]',
            'email' => 'permit_empty|valid_email',
            'payment_term' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $supplierData = [
            'company_id' => getCurrentCompanyId(),
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'payment_term' => $this->request->getPost('payment_term') ?? 30,
            'bank_name' => $this->request->getPost('bank_name'),
            'bank_account' => $this->request->getPost('bank_account'),
            'created_by' => getCurrentUserId()
        ];

        $supplierId = $this->supplierModel->insert($supplierData);

        if ($supplierId) {
            logActivity('create', 'suppliers', "Created supplier: {$supplierData['name']}", $supplierId);
            return redirect()->to('/master/supplier')->with('success', 'Supplier created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create supplier');
    }

    public function edit($id)
    {
        if (!hasPermission('suppliers', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $supplier = $this->supplierModel->where('company_id', $companyId)->find($id);
        
        if (!$supplier) {
            return redirect()->to('/master/supplier')->with('error', 'Supplier not found');
        }

        $data = [
            'title' => 'Edit Supplier',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Suppliers', 'url' => base_url('master/supplier')],
                ['label' => 'Edit']
            ],
            'supplier' => $supplier
        ];

        return view('master/supplier/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('suppliers', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $supplier = $this->supplierModel->where('company_id', $companyId)->find($id);
        
        if (!$supplier) {
            return redirect()->to('/master/supplier')->with('error', 'Supplier not found');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'code' => "required|alpha_numeric|is_unique[suppliers.code,id,{$id}]",
            'name' => 'required|min_length[3]',
            'email' => 'permit_empty|valid_email',
            'payment_term' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $supplierData = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'payment_term' => $this->request->getPost('payment_term') ?? 30,
            'bank_name' => $this->request->getPost('bank_name'),
            'bank_account' => $this->request->getPost('bank_account'),
            'updated_by' => getCurrentUserId()
        ];

        if ($this->supplierModel->update($id, $supplierData)) {
            logActivity('update', 'suppliers', "Updated supplier: {$supplierData['name']}", $id);
            return redirect()->to('/master/supplier')->with('success', 'Supplier updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update supplier');
    }

    public function delete($id)
    {
        if (!hasPermission('suppliers', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $companyId = getCurrentCompanyId();
        $supplier = $this->supplierModel->where('company_id', $companyId)->find($id);
        
        if (!$supplier) {
            return $this->response->setJSON(['success' => false, 'message' => 'Supplier not found']);
        }

        if ($this->supplierModel->delete($id)) {
            logActivity('delete', 'suppliers', "Deleted supplier: {$supplier['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Supplier deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete supplier']);
    }

    public function statement($id)
    {
        if (!hasPermission('suppliers', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $supplier = $this->supplierModel->where('company_id', $companyId)->find($id);
        
        if (!$supplier) {
            return redirect()->to('/master/supplier')->with('error', 'Supplier not found');
        }

        $data = [
            'title' => 'Supplier Statement',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Suppliers', 'url' => base_url('master/supplier')],
                ['label' => 'Statement']
            ],
            'supplier' => $supplier,
            'transactions' => []
        ];

        return view('master/supplier/statement', $data);
    }

    public function export()
    {
        if (!hasPermission('suppliers', 'export')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        return redirect()->to('/master/supplier')->with('info', 'Export feature coming soon');
    }
}
