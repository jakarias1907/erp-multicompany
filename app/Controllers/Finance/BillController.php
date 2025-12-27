<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\BillModel;
use App\Models\SupplierModel;
use App\Models\ProductModel;

class BillController extends BaseController
{
    protected $billModel;
    protected $supplierModel;
    protected $productModel;

    public function __construct()
    {
        $this->billModel = new BillModel();
        $this->supplierModel = new SupplierModel();
        $this->productModel = new ProductModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('bills', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Bills (Accounts Payable)',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Bills']
            ]
        ];

        return view('finance/bill/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('bills', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->billModel->builder();
        $builder->select('bills.*, suppliers.name as supplier_name')
            ->join('suppliers', 'suppliers.id = bills.supplier_id')
            ->where('bills.company_id', $companyId)
            ->where('bills.deleted_at', null);

        if ($searchValue) {
            $builder->groupStart()
                ->like('bills.bill_number', $searchValue)
                ->orLike('suppliers.name', $searchValue)
                ->groupEnd();
        }

        $totalFiltered = $builder->countAllResults(false);
        $bills = $builder->orderBy('bills.created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($bills as $bill) {
            $statusBadges = [
                'draft' => 'secondary',
                'sent' => 'info',
                'partial' => 'warning',
                'paid' => 'success',
                'overdue' => 'danger'
            ];
            
            $statusBadge = '<span class="badge badge-' . ($statusBadges[$bill['status']] ?? 'secondary') . '">' 
                . ucfirst($bill['status']) . '</span>';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('finance/bill/view/' . $bill['id']) . '" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . base_url('finance/bill/print/' . $bill['id']) . '" class="btn btn-sm btn-secondary" title="Print" target="_blank">
                        <i class="fas fa-print"></i>
                    </a>';
            
            if ($bill['status'] != 'paid') {
                $actions .= '
                    <a href="' . base_url('finance/bill/edit/' . $bill['id']) . '" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $bill['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
            }
            
            $actions .= '</div>';

            $data[] = [
                'bill_number' => esc($bill['bill_number']),
                'bill_date' => date('d M Y', strtotime($bill['bill_date'])),
                'supplier_name' => esc($bill['supplier_name']),
                'total' => number_format($bill['total'], 2),
                'paid_amount' => number_format($bill['paid_amount'], 2),
                'status' => $statusBadge,
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->billModel->where('company_id', $companyId)->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('bills', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $suppliers = $this->supplierModel->where('company_id', $companyId)->where('status', 'active')->findAll();
        $products = $this->productModel->where('company_id', $companyId)->findAll();

        // Generate next bill number
        $lastBill = $this->billModel->where('company_id', $companyId)->orderBy('id', 'DESC')->first();
        $nextNumber = 'BILL-' . date('Ym') . '-' . str_pad(($lastBill ? intval(substr($lastBill['bill_number'], -4)) + 1 : 1), 4, '0', STR_PAD_LEFT);

        $data = [
            'title' => 'Create Bill',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Bills', 'url' => base_url('finance/bill')],
                ['label' => 'Create']
            ],
            'suppliers' => $suppliers,
            'products' => $products,
            'billNumber' => $nextNumber
        ];

        return view('finance/bill/create', $data);
    }

    public function store()
    {
        if (!hasPermission('bills', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'supplier_id' => 'required|integer',
            'bill_number' => 'required|max_length[50]',
            'bill_date' => 'required|valid_date',
            'total' => 'required|decimal'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'supplier_id' => $this->request->getPost('supplier_id'),
            'bill_number' => $this->request->getPost('bill_number'),
            'bill_date' => $this->request->getPost('bill_date'),
            'due_date' => $this->request->getPost('due_date'),
            'reference' => $this->request->getPost('reference'),
            'subtotal' => $this->request->getPost('subtotal'),
            'tax_amount' => $this->request->getPost('tax_amount'),
            'discount' => $this->request->getPost('discount'),
            'total' => $this->request->getPost('total'),
            'paid_amount' => 0,
            'status' => 'draft',
            'notes' => $this->request->getPost('notes'),
            'created_by' => getCurrentUserId()
        ];

        if ($this->billModel->insert($data)) {
            logActivity('create', 'bills', "Created bill: {$data['bill_number']}", $this->billModel->getInsertID());
            return redirect()->to('/finance/bill')->with('success', 'Bill created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create bill');
    }

    public function view($id)
    {
        if (!hasPermission('bills', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $db = \Config\Database::connect();
        $bill = $db->table('bills')
            ->select('bills.*, suppliers.name as supplier_name, suppliers.email, suppliers.phone, suppliers.address')
            ->join('suppliers', 'suppliers.id = bills.supplier_id')
            ->where('bills.id', $id)
            ->get()
            ->getRowArray();

        if (!$bill) {
            return redirect()->to('/finance/bill')->with('error', 'Bill not found');
        }

        $data = [
            'title' => 'View Bill',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Bills', 'url' => base_url('finance/bill')],
                ['label' => 'View']
            ],
            'bill' => $bill
        ];

        return view('finance/bill/view', $data);
    }

    public function delete($id)
    {
        if (!hasPermission('bills', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $bill = $this->billModel->find($id);
        if (!$bill) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bill not found']);
        }

        if ($bill['paid_amount'] > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete bill with payments']);
        }

        if ($this->billModel->delete($id)) {
            logActivity('delete', 'bills', "Deleted bill: {$bill['bill_number']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Bill deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete bill']);
    }
}
