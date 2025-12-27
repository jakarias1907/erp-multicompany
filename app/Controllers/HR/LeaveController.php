<?php

namespace App\Controllers\HR;

use App\Controllers\BaseController;
use App\Models\LeaveModel;

class LeaveController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('leave', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Leave Management',
            'breadcrumbs' => [
                ['label' => 'HR', 'url' => '#'],
                ['label' => 'Leave Management']
            ]
        ];

        return view('hr/leave/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('leave', 'read')) {
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
                    <a href="' . base_url('hr/leave/edit/' . $record['id']) . '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
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
        if (!hasPermission('leave', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Create Leave Management',
            'breadcrumbs' => [
                ['label' => 'HR', 'url' => '#'],
                ['label' => 'Leave Management', 'url' => base_url('hr/leave')],
                ['label' => 'Create']
            ]
        ];

        return view('hr/leave/create', $data);
    }

    public function store()
    {
        if (!hasPermission('leave', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'created_by' => getCurrentUserId()
        ];

        if ($this->model->insert($data)) {
            logActivity('create', 'leave', 'Created record', $this->model->getInsertID());
            return redirect()->to('/hr/leave')->with('success', 'Record created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create record');
    }

    public function edit($id)
    {
        if (!hasPermission('leave', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/hr/leave')->with('error', 'Record not found');
        }

        $data = [
            'title' => 'Edit Leave Management',
            'breadcrumbs' => [
                ['label' => 'HR', 'url' => '#'],
                ['label' => 'Leave Management', 'url' => base_url('hr/leave')],
                ['label' => 'Edit']
            ],
            'record' => $record
        ];

        return view('hr/leave/edit', $data);
    }

    public function update($id)
    {
        if (!hasPermission('leave', 'update')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $record = $this->model->find($id);
        if (!$record) {
            return redirect()->to('/hr/leave')->with('error', 'Record not found');
        }

        $data = [
            'updated_by' => getCurrentUserId()
        ];

        if ($this->model->update($id, $data)) {
            logActivity('update', 'leave', 'Updated record', $id);
            return redirect()->to('/hr/leave')->with('success', 'Record updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update record');
    }

    public function delete($id)
    {
        if (!hasPermission('leave', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $record = $this->model->find($id);
        if (!$record) {
            return $this->response->setJSON(['success' => false, 'message' => 'Record not found']);
        }

        if ($this->model->delete($id)) {
            logActivity('delete', 'leave', 'Deleted record', $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Record deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete record']);
    }
}
