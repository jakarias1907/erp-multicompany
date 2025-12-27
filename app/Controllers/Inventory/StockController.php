<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\StockCardModel;

class StockController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockCardModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('stock', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Stock Management',
            'breadcrumbs' => [
                ['label' => 'Inventory', 'url' => '#'],
                ['label' => 'Stock Management']
            ]
        ];

        return view('inventory/stock/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('stock', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->model->builder();
        $builder->where('company_id', $companyId);

        if ($searchValue) {
            $builder->like('id', $searchValue);
        }

        $totalFiltered = $builder->countAllResults(false);
        $records = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($records as $record) {
            $data[] = [
                'id' => $record['id'],
                'actions' => '<div class="btn-group">
                    <a href="' . base_url('inventory/stock/edit/' . $record['id']) . '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $record['id'] . '"><i class="fas fa-trash"></i></button>
                </div>'
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->model->where('company_id', $companyId)->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('stock', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Stock Management',
            'breadcrumbs' => [
                ['label' => 'Inventory', 'url' => '#'],
                ['label' => 'Stock Management', 'url' => base_url('inventory/stock')],
                ['label' => 'Create']
            ]
        ];

        return view('inventory/stock/create', $data);
    }

    public function store()
    {
        if (!hasPermission('stock', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'created_by' => getCurrentUserId()
        ];

        if ($this->model->insert($data)) {
            logActivity('create', 'stock', 'Created record', $this->model->getInsertID());
            return redirect()->to('/inventory/stock')->with('success', 'Record created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create record');
    }

    public function edit($id)
    {
        if (!hasPermission('stock', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/inventory/stock')->with('error', 'Record not found');
        }

        $data = [
            'title' => 'Edit Stock Management',
            'breadcrumbs' => [
                ['label' => 'Inventory', 'url' => '#'],
                ['label' => 'Stock Management', 'url' => base_url('inventory/stock')],
                ['label' => 'Edit']
            ],
            'record' => $record
        ];

        return view('inventory/stock/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('stock', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/inventory/stock')->with('error', 'Record not found');
        }

        $data = [
            'updated_by' => getCurrentUserId()
        ];

        if ($this->model->update($id, $data)) {
            logActivity('update', 'stock', 'Updated record', $id);
            return redirect()->to('/inventory/stock')->with('success', 'Record updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update record');
    }

    public function delete($id)
    {
        if (!hasPermission('stock', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $record = $this->model->find($id);
        if (!$record) {
            return $this->response->setJSON(['success' => false, 'message' => 'Record not found']);
        }

        if ($this->model->delete($id)) {
            logActivity('delete', 'stock', 'Deleted record', $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Record deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete record']);
    }
}
