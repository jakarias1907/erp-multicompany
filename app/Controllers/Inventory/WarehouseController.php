<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\WarehouseModel;

class WarehouseController extends BaseController
{
    protected $warehouseModel;

    public function __construct()
    {
        $this->warehouseModel = new WarehouseModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('warehouses', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Warehouses',
            'breadcrumbs' => [
                ['label' => 'Inventory', 'url' => '#'],
                ['label' => 'Warehouses']
            ]
        ];

        return view('inventory/warehouse/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('warehouses', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->warehouseModel->builder();
        $builder->where('company_id', $companyId);

        if ($searchValue) {
            $builder->like('name', $searchValue);
        }

        $totalFiltered = $builder->countAllResults(false);
        $warehouses = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($warehouses as $warehouse) {
            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('inventory/warehouse/edit/' . $warehouse['id']) . '" class="btn btn-sm btn-info" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $warehouse['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';

            $data[] = [
                'name' => esc($warehouse['name']),
                'address' => esc($warehouse['address'] ?? '-'),
                'phone' => esc($warehouse['phone'] ?? '-'),
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->warehouseModel->where('company_id', $companyId)->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('warehouses', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Warehouse',
            'breadcrumbs' => [
                ['label' => 'Inventory', 'url' => '#'],
                ['label' => 'Warehouses', 'url' => base_url('inventory/warehouse')],
                ['label' => 'Create']
            ]
        ];

        return view('inventory/warehouse/create', $data);
    }

    public function store()
    {
        if (!hasPermission('warehouses', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|max_length[255]',
            'address' => 'max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'created_by' => getCurrentUserId()
        ];

        if ($this->warehouseModel->insert($data)) {
            logActivity('create', 'warehouses', "Created warehouse: {$data['name']}", $this->warehouseModel->getInsertID());
            return redirect()->to('/inventory/warehouse')->with('success', 'Warehouse created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create warehouse');
    }

    public function edit($id)
    {
        if (!hasPermission('warehouses', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $warehouse = $this->warehouseModel->find($id);
        if (!$warehouse) {
            return redirect()->to('/inventory/warehouse')->with('error', 'Warehouse not found');
        }

        $data = [
            'title' => 'Edit Warehouse',
            'breadcrumbs' => [
                ['label' => 'Inventory', 'url' => '#'],
                ['label' => 'Warehouses', 'url' => base_url('inventory/warehouse')],
                ['label' => 'Edit']
            ],
            'warehouse' => $warehouse
        ];

        return view('inventory/warehouse/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('warehouses', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $warehouse = $this->warehouseModel->find($id);
        if (!$warehouse) {
            return redirect()->to('/inventory/warehouse')->with('error', 'Warehouse not found');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|max_length[255]',
            'address' => 'max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'updated_by' => getCurrentUserId()
        ];

        if ($this->warehouseModel->update($id, $data)) {
            logActivity('update', 'warehouses', "Updated warehouse: {$data['name']}", $id);
            return redirect()->to('/inventory/warehouse')->with('success', 'Warehouse updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update warehouse');
    }

    public function delete($id)
    {
        if (!hasPermission('warehouses', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $warehouse = $this->warehouseModel->find($id);
        if (!$warehouse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse not found']);
        }

        if ($this->warehouseModel->delete($id)) {
            logActivity('delete', 'warehouses', "Deleted warehouse: {$warehouse['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Warehouse deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete warehouse']);
    }
}
