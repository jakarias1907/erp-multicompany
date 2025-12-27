<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\QuotationModel;

class QuotationController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new QuotationModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('sales', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Quotations',
            'breadcrumbs' => [
                ['label' => 'Sales', 'url' => '#'],
                ['label' => 'Quotations']
            ]
        ];

        return view('sales/quotation/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('sales', 'read')) {
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
                    <a href="' . base_url('sales/quotation/edit/' . $record['id']) . '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
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
        if (!hasPermission('sales', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Quotations',
            'breadcrumbs' => [
                ['label' => 'Sales', 'url' => '#'],
                ['label' => 'Quotations', 'url' => base_url('sales/quotation')],
                ['label' => 'Create']
            ]
        ];

        return view('sales/quotation/create', $data);
    }

    public function store()
    {
        if (!hasPermission('sales', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'created_by' => getCurrentUserId()
        ];

        if ($this->model->insert($data)) {
            logActivity('create', 'sales', 'Created record', $this->model->getInsertID());
            return redirect()->to('/sales/quotation')->with('success', 'Record created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create record');
    }

    public function edit($id)
    {
        if (!hasPermission('sales', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/sales/quotation')->with('error', 'Record not found');
        }

        $data = [
            'title' => 'Edit Quotations',
            'breadcrumbs' => [
                ['label' => 'Sales', 'url' => '#'],
                ['label' => 'Quotations', 'url' => base_url('sales/quotation')],
                ['label' => 'Edit']
            ],
            'record' => $record
        ];

        return view('sales/quotation/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('sales', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/sales/quotation')->with('error', 'Record not found');
        }

        $data = [
            'updated_by' => getCurrentUserId()
        ];

        if ($this->model->update($id, $data)) {
            logActivity('update', 'sales', 'Updated record', $id);
            return redirect()->to('/sales/quotation')->with('success', 'Record updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update record');
    }

    public function delete($id)
    {
        if (!hasPermission('sales', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $record = $this->model->find($id);
        if (!$record) {
            return $this->response->setJSON(['success' => false, 'message' => 'Record not found']);
        }

        if ($this->model->delete($id)) {
            logActivity('delete', 'sales', 'Deleted record', $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Record deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete record']);
    }
}
