<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;

class InvoiceController extends BaseController
{
    protected $invoiceModel;
    protected $customerModel;
    protected $productModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('invoices', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Invoices',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Invoices']
            ]
        ];

        return view('finance/invoice/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('invoices', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->invoiceModel->builder();
        $builder->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.deleted_at IS NULL');

        if ($searchValue) {
            $builder->groupStart()
                ->like('invoices.invoice_number', $searchValue)
                ->orLike('customers.name', $searchValue)
                ->groupEnd();
        }

        $totalFiltered = $builder->countAllResults(false);

        $invoices = $builder->orderBy('invoices.created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($invoices as $invoice) {
            $statusBadges = [
                'draft' => 'secondary',
                'sent' => 'info',
                'partial' => 'warning',
                'paid' => 'success',
                'overdue' => 'danger'
            ];
            
            $statusBadge = '<span class="badge badge-' . ($statusBadges[$invoice['status']] ?? 'secondary') . '">' 
                . ucfirst($invoice['status']) . '</span>';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('finance/invoice/view/' . $invoice['id']) . '" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . base_url('finance/invoice/print/' . $invoice['id']) . '" class="btn btn-sm btn-secondary" title="Print" target="_blank">
                        <i class="fas fa-print"></i>
                    </a>';
            
            if ($invoice['status'] != 'paid') {
                $actions .= '
                    <a href="' . base_url('finance/invoice/edit/' . $invoice['id']) . '" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-success btn-payment" data-id="' . $invoice['id'] . '" title="Payment">
                        <i class="fas fa-dollar-sign"></i>
                    </button>';
            }
            
            if ($invoice['status'] == 'draft') {
                $actions .= '
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $invoice['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
            }
            
            $actions .= '</div>';

            $data[] = [
                'invoice_number' => esc($invoice['invoice_number']),
                'invoice_date' => formatDate($invoice['invoice_date']),
                'customer_name' => esc($invoice['customer_name']),
                'total_amount' => formatCurrency($invoice['total_amount']),
                'paid_amount' => formatCurrency($invoice['paid_amount'] ?? 0),
                'balance' => formatCurrency($invoice['total_amount'] - ($invoice['paid_amount'] ?? 0)),
                'status' => $statusBadge,
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->invoiceModel->where('company_id', $companyId)->where('deleted_at IS NULL')->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('invoices', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        
        $data = [
            'title' => 'Create Invoice',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Invoices', 'url' => base_url('finance/invoice')],
                ['label' => 'Create']
            ],
            'customers' => $this->customerModel->where('company_id', $companyId)->findAll(),
            'products' => $this->productModel->where('company_id', $companyId)->findAll()
        ];

        return view('finance/invoice/create', $data);
    }

    public function store()
    {
        if (!hasPermission('invoices', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Calculate totals
            $subtotal = 0;
            $items = $this->request->getPost('items');
            
            foreach ($items as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            }

            $taxRate = $this->request->getPost('tax_rate') ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Save invoice header
            $invoiceData = [
                'company_id' => getCurrentCompanyId(),
                'customer_id' => $this->request->getPost('customer_id'),
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $this->request->getPost('invoice_date'),
                'due_date' => $this->request->getPost('due_date'),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'draft',
                'notes' => $this->request->getPost('notes'),
                'created_by' => getCurrentUserId()
            ];

            $invoiceId = $this->invoiceModel->insert($invoiceData);

            // Save invoice items
            foreach ($items as $item) {
                $db->table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0)
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to create invoice');
            }

            logActivity('create', 'invoices', "Created invoice: {$invoiceNumber}", $invoiceId);
            return redirect()->to('/finance/invoice')->with('success', 'Invoice created successfully');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function generateInvoiceNumber()
    {
        $companyId = getCurrentCompanyId();
        $prefix = 'INV';
        $date = date('Ymd');
        
        $lastInvoice = $this->invoiceModel
            ->where('company_id', $companyId)
            ->like('invoice_number', $prefix . $date)
            ->orderBy('id', 'DESC')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice['invoice_number'], -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . sprintf('%04d', $newNumber);
    }

    public function view($id)
    {
        if (!hasPermission('invoices', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $invoice = $this->invoiceModel->getWithCustomer($id);
        
        if (!$invoice || $invoice['company_id'] != $companyId) {
            return redirect()->to('/finance/invoice')->with('error', 'Invoice not found');
        }

        $db = \Config\Database::connect();
        $items = $db->table('invoice_items')
            ->select('invoice_items.*, products.name as product_name, products.sku')
            ->join('products', 'products.id = invoice_items.product_id')
            ->where('invoice_items.invoice_id', $id)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'View Invoice',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Invoices', 'url' => base_url('finance/invoice')],
                ['label' => 'View']
            ],
            'invoice' => $invoice,
            'items' => $items
        ];

        return view('finance/invoice/view', $data);
    }

    public function print($id)
    {
        if (!hasPermission('invoices', 'print')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $invoice = $this->invoiceModel->getWithCustomer($id);
        
        if (!$invoice || $invoice['company_id'] != $companyId) {
            return redirect()->to('/finance/invoice')->with('error', 'Invoice not found');
        }

        $db = \Config\Database::connect();
        $items = $db->table('invoice_items')
            ->select('invoice_items.*, products.name as product_name')
            ->join('products', 'products.id = invoice_items.product_id')
            ->where('invoice_items.invoice_id', $id)
            ->get()
            ->getResultArray();

        // Get company info
        $company = $db->table('companies')->where('id', $companyId)->get()->getRowArray();

        $data = [
            'invoice' => $invoice,
            'items' => $items,
            'company' => $company
        ];

        // Generate PDF using DOMPDF
        $dompdf = new \Dompdf\Dompdf();
        $html = view('finance/invoice/pdf_template', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->stream('invoice_' . $invoice['invoice_number'] . '.pdf');
    }

    public function delete($id)
    {
        if (!hasPermission('invoices', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $companyId = getCurrentCompanyId();
        $invoice = $this->invoiceModel->where('company_id', $companyId)->find($id);
        
        if (!$invoice) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invoice not found']);
        }

        if ($invoice['status'] != 'draft') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only draft invoices can be deleted']);
        }

        if ($this->invoiceModel->delete($id)) {
            logActivity('delete', 'invoices', "Deleted invoice: {$invoice['invoice_number']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Invoice deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete invoice']);
    }
}
