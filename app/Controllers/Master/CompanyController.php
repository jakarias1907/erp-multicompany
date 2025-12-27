<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyController extends BaseController
{
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        helper(['form', 'url']);
    }

    /**
     * List all companies
     */
    public function index()
    {
        $data = [
            'title' => 'Companies',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Companies']
            ]
        ];

        return view('master/company/index', $data);
    }

    /**
     * DataTables server-side processing
     */
    public function datatable()
    {
        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';

        // Base query
        $builder = $this->companyModel->builder();

        // Search
        if ($searchValue) {
            $builder->groupStart()
                ->like('name', $searchValue)
                ->orLike('code', $searchValue)
                ->orLike('email', $searchValue)
                ->groupEnd();
        }

        // Total records
        $totalRecords = $this->companyModel->countAll();
        $totalFiltered = $builder->countAllResults(false);

        // Get data
        $companies = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // Format data for DataTables
        $data = [];
        foreach ($companies as $company) {
            $statusBadge = $company['status'] == 'active' 
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $logo = $company['logo'] 
                ? '<img src="' . base_url('uploads/companies/' . $company['logo']) . '" width="40" class="img-thumbnail">'
                : '<span class="badge badge-secondary">No Logo</span>';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('master/company/edit/' . $company['id']) . '" class="btn btn-sm btn-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $company['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'code' => esc($company['code']),
                'logo' => $logo,
                'name' => esc($company['name']),
                'email' => esc($company['email'] ?? '-'),
                'phone' => esc($company['phone'] ?? '-'),
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
        $data = [
            'title' => 'Create Company',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Companies', 'url' => base_url('master/company')],
                ['label' => 'Create']
            ]
        ];

        return view('master/company/create', $data);
    }

    /**
     * Save new company
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'code' => 'required|alpha_numeric|is_unique[companies.code]|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'tax_id' => 'permit_empty|max_length[100]',
            'logo' => 'permit_empty|max_size[logo,2048]|is_image[logo]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'tax_id' => $this->request->getPost('tax_id'),
            'status' => $this->request->getPost('status') ?? 'active'
        ];

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = $logo->getRandomName();
            $logo->move(WRITEPATH . '../public/uploads/companies', $newName);
            $data['logo'] = $newName;
        }

        if ($this->companyModel->insert($data)) {
            logActivity('create', 'companies', "Created company: {$data['name']}", $this->companyModel->getInsertID());
            return redirect()->to('master/company')->with('success', 'Company created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create company');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $company = $this->companyModel->find($id);

        if (!$company) {
            return redirect()->to('master/company')->with('error', 'Company not found');
        }

        $data = [
            'title' => 'Edit Company',
            'company' => $company,
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Companies', 'url' => base_url('master/company')],
                ['label' => 'Edit']
            ]
        ];

        return view('master/company/edit', $data);
    }

    /**
     * Update company
     */
    public function update($id)
    {
        $company = $this->companyModel->find($id);

        if (!$company) {
            return redirect()->to('master/company')->with('error', 'Company not found');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'code' => "required|alpha_numeric|is_unique[companies.code,id,{$id}]|max_length[50]",
            'email' => 'permit_empty|valid_email',
            'tax_id' => 'permit_empty|max_length[100]',
            'logo' => 'permit_empty|max_size[logo,2048]|is_image[logo]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'tax_id' => $this->request->getPost('tax_id'),
            'status' => $this->request->getPost('status') ?? 'active'
        ];

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            // Delete old logo
            if ($company['logo'] && file_exists(WRITEPATH . '../public/uploads/companies/' . $company['logo'])) {
                unlink(WRITEPATH . '../public/uploads/companies/' . $company['logo']);
            }

            $newName = $logo->getRandomName();
            $logo->move(WRITEPATH . '../public/uploads/companies', $newName);
            $data['logo'] = $newName;
        }

        if ($this->companyModel->update($id, $data)) {
            logActivity('update', 'companies', "Updated company: {$data['name']}", $id);
            return redirect()->to('master/company')->with('success', 'Company updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update company');
    }

    /**
     * Delete company
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $company = $this->companyModel->find($id);

        if (!$company) {
            return $this->response->setJSON(['success' => false, 'message' => 'Company not found']);
        }

        // Delete logo file
        if ($company['logo'] && file_exists(WRITEPATH . '../public/uploads/companies/' . $company['logo'])) {
            unlink(WRITEPATH . '../public/uploads/companies/' . $company['logo']);
        }

        if ($this->companyModel->delete($id)) {
            logActivity('delete', 'companies', "Deleted company: {$company['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Company deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete company']);
    }
}
