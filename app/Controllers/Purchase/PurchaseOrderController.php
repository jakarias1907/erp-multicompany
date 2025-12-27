<?php

namespace App\Controllers\Purchase;

use App\Controllers\BaseController;
use App\Models\PurchaseOrderModel;

class PurchaseOrderController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new PurchaseOrderModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('purchases', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Purchase Orders',
            'breadcrumbs' => [
                ['label' => 'Purchase', 'url' => '#'],
                ['label' => 'Purchase Orders']
            ]
        ];

        return view('purchase/purchaseorder/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('purchases', 'read')) {
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
                    <a href="' . base_url('purchase/purchaseorder/edit/' . $record['id']) . '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
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
        if (!hasPermission('purchases', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Purchase Orders',
            'breadcrumbs' => [
                ['label' => 'Purchase', 'url' => '#'],
                ['label' => 'Purchase Orders', 'url' => base_url('purchase/purchaseorder')],
                ['label' => 'Create']
            ]
        ];

        return view('purchase/purchaseorder/create', $data);
    }

    public function store()
    {
        if (!hasPermission('purchases', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'created_by' => getCurrentUserId()
        ];

        if ($this->model->insert($data)) {
            logActivity('create', 'purchases', 'Created record', $this->model->getInsertID());
            return redirect()->to('/purchase/purchaseorder')->with('success', 'Record created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create record');
    }

    public function edit($id)
    {
        if (!hasPermission('purchases', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/purchase/purchaseorder')->with('error', 'Record not found');
        }

        $data = [
            'title' => 'Edit Purchase Orders',
            'breadcrumbs' => [
                ['label' => 'Purchase', 'url' => '#'],
                ['label' => 'Purchase Orders', 'url' => base_url('purchase/purchaseorder')],
                ['label' => 'Edit']
            ],
            'record' => $record
        ];

        return view('purchase/purchaseorder/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('purchases', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/purchase/purchaseorder')->with('error', 'Record not found');
        }

        $data = [
            'updated_by' => getCurrentUserId()
        ];

        if ($this->model->update($id, $data)) {
            logActivity('update', 'purchases', 'Updated record', $id);
            return redirect()->to('/purchase/purchaseorder')->with('success', 'Record updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update record');
    }

    public function delete($id)
    {
        if (!hasPermission('purchases', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $record = $this->model->find($id);
        if (!$record) {
            return $this->response->setJSON(['success' => false, 'message' => 'Record not found']);
        }

        if ($this->model->delete($id)) {
            logActivity('delete', 'purchases', 'Deleted record', $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Record deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete record']);
    }
}
