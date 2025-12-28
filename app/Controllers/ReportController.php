<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\PdfReportLibrary;
use App\Libraries\ExcelExportLibrary;
use App\Models\InvoiceModel;
use App\Models\SalesOrderModel;
use App\Models\PurchaseOrderModel;
use App\Models\StockCardModel;
use App\Models\CustomerModel;
use App\Models\SupplierModel;
use App\Models\JournalEntryModel;
use App\Models\JournalEntryLineModel;
use App\Models\ChartOfAccountModel;
use App\Models\AttendanceModel;
use App\Models\PayrollModel;
use App\Models\EmployeeModel;

class ReportController extends BaseController
{
    protected $invoiceModel;
    protected $salesOrderModel;
    protected $purchaseOrderModel;
    protected $stockCardModel;
    protected $customerModel;
    protected $supplierModel;
    protected $journalEntryModel;
    protected $journalEntryLineModel;
    protected $chartOfAccountModel;
    protected $attendanceModel;
    protected $payrollModel;
    protected $employeeModel;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->invoiceModel = new InvoiceModel();
        $this->salesOrderModel = new SalesOrderModel();
        $this->purchaseOrderModel = new PurchaseOrderModel();
        $this->stockCardModel = new StockCardModel();
        $this->customerModel = new CustomerModel();
        $this->supplierModel = new SupplierModel();
        $this->journalEntryModel = new JournalEntryModel();
        $this->journalEntryLineModel = new JournalEntryLineModel();
        $this->chartOfAccountModel = new ChartOfAccountModel();
        $this->attendanceModel = new AttendanceModel();
        $this->payrollModel = new PayrollModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function index()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Reports Dashboard',
            'breadcrumbs' => [
                ['label' => 'Reports']
            ]
        ];

        return view('reports/index', $data);
    }

    // ====================== SALES REPORT ======================
    public function salesReport()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        $customerId = $this->request->getGet('customer_id');

        $data = [
            'title' => 'Sales Summary Report',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Sales Summary']
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'customer_id' => $customerId,
            'customers' => $this->customerModel->findAll()
        ];

        return view('reports/sales_report', $data);
    }

    public function salesReportPdf()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $builder = $this->invoiceModel
            ->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->where('invoices.invoice_date >=', $startDate)
            ->where('invoices.invoice_date <=', $endDate)
            ->where('invoices.company_id', session()->get('current_company_id'));
        
        $salesData = $builder->findAll();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Sales Summary Report');
        $pdf->setReportPeriod($startDate . ' to ' . $endDate);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(30, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Invoice No', 1, 0, 'C', true);
        $pdf->Cell(60, 7, 'Customer', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Status', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $total = 0;
        foreach ($salesData as $row) {
            $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['invoice_date'])), 1, 0, 'C');
            $pdf->Cell(40, 6, $row['invoice_number'], 1, 0, 'L');
            $pdf->Cell(60, 6, $row['customer_name'], 1, 0, 'L');
            $pdf->Cell(30, 6, number_format($row['total_amount'], 2), 1, 0, 'R');
            $pdf->Cell(25, 6, ucfirst($row['status']), 1, 1, 'C');
            $total += $row['total_amount'];
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($total, 2), 1, 0, 'R');
        $pdf->Cell(25, 7, '', 1, 1, 'C');

        $pdf->Output('sales_report_' . date('Ymd') . '.pdf', 'D');
    }

    public function salesReportExcel()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $builder = $this->invoiceModel
            ->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->where('invoices.invoice_date >=', $startDate)
            ->where('invoices.invoice_date <=', $endDate)
            ->where('invoices.company_id', session()->get('current_company_id'));
        
        $salesData = $builder->findAll();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('E');
        $excel->addReportTitle('Sales Summary Report', 'E');
        $excel->addReportPeriod($startDate . ' to ' . $endDate, 'E');

        $headers = ['Date', 'Invoice No', 'Customer', 'Amount', 'Status'];
        $excel->addTableHeader($headers);

        $total = 0;
        foreach ($salesData as $row) {
            $excel->addTableRow([
                date('d/m/Y', strtotime($row['invoice_date'])),
                $row['invoice_number'],
                $row['customer_name'],
                number_format($row['total_amount'], 2),
                ucfirst($row['status'])
            ]);
            $total += $row['total_amount'];
        }

        $excel->addTableRow(['', '', 'TOTAL', number_format($total, 2), ''], true);
        $excel->applyTableBorders($excel->currentRow - count($salesData) - 2, $excel->currentRow - 1, 'E');
        $excel->autoSizeColumns('E');

        $excel->download('sales_report_' . date('Ymd'));
    }

    // ====================== PURCHASE REPORT ======================
    public function purchaseReport()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        $data = [
            'title' => 'Purchase Report',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Purchase']
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'suppliers' => $this->supplierModel->findAll()
        ];

        return view('reports/purchase_report', $data);
    }

    public function purchaseReportPdf()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $db = \Config\Database::connect();
        $builder = $db->table('purchase_orders po')
            ->select('po.*, s.name as supplier_name')
            ->join('suppliers s', 's.id = po.supplier_id')
            ->where('po.order_date >=', $startDate)
            ->where('po.order_date <=', $endDate)
            ->where('po.company_id', session()->get('current_company_id'));
        
        $purchaseData = $builder->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Purchase Report');
        $pdf->setReportPeriod($startDate . ' to ' . $endDate);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(30, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'PO Number', 1, 0, 'C', true);
        $pdf->Cell(60, 7, 'Supplier', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Status', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $total = 0;
        foreach ($purchaseData as $row) {
            $pdf->Cell(30, 6, date('d/m/Y', strtotime($row['order_date'])), 1, 0, 'C');
            $pdf->Cell(40, 6, $row['order_number'], 1, 0, 'L');
            $pdf->Cell(60, 6, $row['supplier_name'], 1, 0, 'L');
            $pdf->Cell(30, 6, number_format($row['total'], 2), 1, 0, 'R');
            $pdf->Cell(25, 6, ucfirst($row['status']), 1, 1, 'C');
            $total += $row['total'];
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($total, 2), 1, 0, 'R');
        $pdf->Cell(25, 7, '', 1, 1, 'C');

        $pdf->Output('purchase_report_' . date('Ymd') . '.pdf', 'D');
    }

    public function purchaseReportExcel()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $db = \Config\Database::connect();
        $builder = $db->table('purchase_orders po')
            ->select('po.*, s.name as supplier_name')
            ->join('suppliers s', 's.id = po.supplier_id')
            ->where('po.order_date >=', $startDate)
            ->where('po.order_date <=', $endDate)
            ->where('po.company_id', session()->get('current_company_id'));
        
        $purchaseData = $builder->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('E');
        $excel->addReportTitle('Purchase Report', 'E');
        $excel->addReportPeriod($startDate . ' to ' . $endDate, 'E');

        $headers = ['Date', 'PO Number', 'Supplier', 'Amount', 'Status'];
        $excel->addTableHeader($headers);

        $total = 0;
        foreach ($purchaseData as $row) {
            $excel->addTableRow([
                date('d/m/Y', strtotime($row['order_date'])),
                $row['order_number'],
                $row['supplier_name'],
                number_format($row['total'], 2),
                ucfirst($row['status'])
            ]);
            $total += $row['total'];
        }

        $excel->addTableRow(['', '', 'TOTAL', number_format($total, 2), ''], true);
        $excel->applyTableBorders($excel->currentRow - count($purchaseData) - 2, $excel->currentRow - 1, 'E');
        $excel->autoSizeColumns('E');

        $excel->download('purchase_report_' . date('Ymd'));
    }

    // ====================== INVENTORY/STOCK REPORT ======================
    public function inventoryReport()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Inventory Report',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Inventory']
            ]
        ];

        return view('reports/inventory_report', $data);
    }

    public function inventoryReportPdf()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_cards sc')
            ->select('sc.*, p.name as product_name, p.code as product_code, w.name as warehouse_name')
            ->join('products p', 'p.id = sc.product_id')
            ->join('warehouses w', 'w.id = sc.warehouse_id')
            ->where('sc.company_id', session()->get('current_company_id'));
        
        $stockData = $builder->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Inventory Report');
        $pdf->setReportPeriod('As of ' . date('d/m/Y'));

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(30, 7, 'Code', 1, 0, 'C', true);
        $pdf->Cell(60, 7, 'Product', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'Warehouse', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Quantity', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Value', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $totalQty = 0;
        $totalValue = 0;
        foreach ($stockData as $row) {
            $value = $row['quantity'] * ($row['unit_cost'] ?? 0);
            $pdf->Cell(30, 6, $row['product_code'], 1, 0, 'L');
            $pdf->Cell(60, 6, $row['product_name'], 1, 0, 'L');
            $pdf->Cell(45, 6, $row['warehouse_name'], 1, 0, 'L');
            $pdf->Cell(25, 6, number_format($row['quantity'], 2), 1, 0, 'R');
            $pdf->Cell(25, 6, number_format($value, 2), 1, 1, 'R');
            $totalQty += $row['quantity'];
            $totalValue += $value;
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(135, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(25, 7, number_format($totalQty, 2), 1, 0, 'R');
        $pdf->Cell(25, 7, number_format($totalValue, 2), 1, 1, 'R');

        $pdf->Output('inventory_report_' . date('Ymd') . '.pdf', 'D');
    }

    public function inventoryReportExcel()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_cards sc')
            ->select('sc.*, p.name as product_name, p.code as product_code, w.name as warehouse_name')
            ->join('products p', 'p.id = sc.product_id')
            ->join('warehouses w', 'w.id = sc.warehouse_id')
            ->where('sc.company_id', session()->get('current_company_id'));
        
        $stockData = $builder->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('E');
        $excel->addReportTitle('Inventory Report', 'E');
        $excel->addReportPeriod('As of ' . date('d/m/Y'), 'E');

        $headers = ['Code', 'Product', 'Warehouse', 'Quantity', 'Value'];
        $excel->addTableHeader($headers);

        $totalQty = 0;
        $totalValue = 0;
        foreach ($stockData as $row) {
            $value = $row['quantity'] * ($row['unit_cost'] ?? 0);
            $excel->addTableRow([
                $row['product_code'],
                $row['product_name'],
                $row['warehouse_name'],
                number_format($row['quantity'], 2),
                number_format($value, 2)
            ]);
            $totalQty += $row['quantity'];
            $totalValue += $value;
        }

        $excel->addTableRow(['', '', 'TOTAL', number_format($totalQty, 2), number_format($totalValue, 2)], true);
        $excel->applyTableBorders($excel->currentRow - count($stockData) - 2, $excel->currentRow - 1, 'E');
        $excel->autoSizeColumns('E');

        $excel->download('inventory_report_' . date('Ymd'));
    }

    // ====================== CUSTOMER STATEMENT ======================
    public function customerStatement()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Customer Statement',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Customer Statement']
            ],
            'customers' => $this->customerModel->findAll()
        ];

        return view('reports/customer_statement', $data);
    }

    public function customerStatementPdf()
    {
        $customerId = $this->request->getGet('customer_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $customer = $this->customerModel->find($customerId);
        
        $builder = $this->invoiceModel
            ->where('customer_id', $customerId)
            ->where('invoice_date >=', $startDate)
            ->where('invoice_date <=', $endDate);
        
        $invoices = $builder->findAll();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Customer Statement');
        $pdf->setReportPeriod($startDate . ' to ' . $endDate);

        // Customer info
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'Customer: ' . ($customer['name'] ?? 'Unknown'), 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Address: ' . ($customer['address'] ?? '-'), 0, 1);
        $pdf->Cell(0, 5, 'Phone: ' . ($customer['phone'] ?? '-'), 0, 1);
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(30, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Invoice No', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Paid', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'Balance', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $totalAmount = 0;
        $totalPaid = 0;
        foreach ($invoices as $invoice) {
            $balance = $invoice['total_amount'] - $invoice['paid_amount'];
            $pdf->Cell(30, 6, date('d/m/Y', strtotime($invoice['invoice_date'])), 1, 0, 'C');
            $pdf->Cell(40, 6, $invoice['invoice_number'], 1, 0, 'L');
            $pdf->Cell(35, 6, number_format($invoice['total_amount'], 2), 1, 0, 'R');
            $pdf->Cell(35, 6, number_format($invoice['paid_amount'], 2), 1, 0, 'R');
            $pdf->Cell(45, 6, number_format($balance, 2), 1, 1, 'R');
            $totalAmount += $invoice['total_amount'];
            $totalPaid += $invoice['paid_amount'];
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(70, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(35, 7, number_format($totalAmount, 2), 1, 0, 'R');
        $pdf->Cell(35, 7, number_format($totalPaid, 2), 1, 0, 'R');
        $pdf->Cell(45, 7, number_format($totalAmount - $totalPaid, 2), 1, 1, 'R');

        $pdf->Output('customer_statement_' . date('Ymd') . '.pdf', 'D');
    }

    public function customerStatementExcel()
    {
        $customerId = $this->request->getGet('customer_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $customer = $this->customerModel->find($customerId);
        
        $builder = $this->invoiceModel
            ->where('customer_id', $customerId)
            ->where('invoice_date >=', $startDate)
            ->where('invoice_date <=', $endDate);
        
        $invoices = $builder->findAll();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('E');
        $excel->addReportTitle('Customer Statement', 'E');
        $excel->addReportPeriod($startDate . ' to ' . $endDate, 'E');

        // Customer info
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'Customer: ' . ($customer['name'] ?? 'Unknown'));
        $excel->currentRow++;
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'Address: ' . ($customer['address'] ?? '-'));
        $excel->currentRow++;
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'Phone: ' . ($customer['phone'] ?? '-'));
        $excel->currentRow += 2;

        $headers = ['Date', 'Invoice No', 'Amount', 'Paid', 'Balance'];
        $excel->addTableHeader($headers);

        $totalAmount = 0;
        $totalPaid = 0;
        foreach ($invoices as $invoice) {
            $balance = $invoice['total_amount'] - $invoice['paid_amount'];
            $excel->addTableRow([
                date('d/m/Y', strtotime($invoice['invoice_date'])),
                $invoice['invoice_number'],
                number_format($invoice['total_amount'], 2),
                number_format($invoice['paid_amount'], 2),
                number_format($balance, 2)
            ]);
            $totalAmount += $invoice['total_amount'];
            $totalPaid += $invoice['paid_amount'];
        }

        $excel->addTableRow(['', 'TOTAL', number_format($totalAmount, 2), number_format($totalPaid, 2), number_format($totalAmount - $totalPaid, 2)], true);
        $excel->applyTableBorders($excel->currentRow - count($invoices) - 2, $excel->currentRow - 1, 'E');
        $excel->autoSizeColumns('E');

        $excel->download('customer_statement_' . date('Ymd'));
    }

    // ====================== SUPPLIER STATEMENT ======================
    public function supplierStatement()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Supplier Statement',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Supplier Statement']
            ],
            'suppliers' => $this->supplierModel->findAll()
        ];

        return view('reports/supplier_statement', $data);
    }

    public function supplierStatementPdf()
    {
        $supplierId = $this->request->getGet('supplier_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $supplier = $this->supplierModel->find($supplierId);
        
        $db = \Config\Database::connect();
        $builder = $db->table('bills')
            ->where('supplier_id', $supplierId)
            ->where('bill_date >=', $startDate)
            ->where('bill_date <=', $endDate);
        
        $bills = $builder->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Supplier Statement');
        $pdf->setReportPeriod($startDate . ' to ' . $endDate);

        // Supplier info
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'Supplier: ' . ($supplier['name'] ?? 'Unknown'), 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Address: ' . ($supplier['address'] ?? '-'), 0, 1);
        $pdf->Cell(0, 5, 'Phone: ' . ($supplier['phone'] ?? '-'), 0, 1);
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(30, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Bill No', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Paid', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'Balance', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $totalAmount = 0;
        $totalPaid = 0;
        foreach ($bills as $bill) {
            $balance = $bill['total_amount'] - $bill['paid_amount'];
            $pdf->Cell(30, 6, date('d/m/Y', strtotime($bill['bill_date'])), 1, 0, 'C');
            $pdf->Cell(40, 6, $bill['bill_number'], 1, 0, 'L');
            $pdf->Cell(35, 6, number_format($bill['total_amount'], 2), 1, 0, 'R');
            $pdf->Cell(35, 6, number_format($bill['paid_amount'], 2), 1, 0, 'R');
            $pdf->Cell(45, 6, number_format($balance, 2), 1, 1, 'R');
            $totalAmount += $bill['total_amount'];
            $totalPaid += $bill['paid_amount'];
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(70, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(35, 7, number_format($totalAmount, 2), 1, 0, 'R');
        $pdf->Cell(35, 7, number_format($totalPaid, 2), 1, 0, 'R');
        $pdf->Cell(45, 7, number_format($totalAmount - $totalPaid, 2), 1, 1, 'R');

        $pdf->Output('supplier_statement_' . date('Ymd') . '.pdf', 'D');
    }

    public function supplierStatementExcel()
    {
        $supplierId = $this->request->getGet('supplier_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $supplier = $this->supplierModel->find($supplierId);
        
        $db = \Config\Database::connect();
        $builder = $db->table('bills')
            ->where('supplier_id', $supplierId)
            ->where('bill_date >=', $startDate)
            ->where('bill_date <=', $endDate);
        
        $bills = $builder->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('E');
        $excel->addReportTitle('Supplier Statement', 'E');
        $excel->addReportPeriod($startDate . ' to ' . $endDate, 'E');

        // Supplier info
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'Supplier: ' . ($supplier['name'] ?? 'Unknown'));
        $excel->currentRow++;
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'Address: ' . ($supplier['address'] ?? '-'));
        $excel->currentRow++;
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'Phone: ' . ($supplier['phone'] ?? '-'));
        $excel->currentRow += 2;

        $headers = ['Date', 'Bill No', 'Amount', 'Paid', 'Balance'];
        $excel->addTableHeader($headers);

        $totalAmount = 0;
        $totalPaid = 0;
        foreach ($bills as $bill) {
            $balance = $bill['total_amount'] - $bill['paid_amount'];
            $excel->addTableRow([
                date('d/m/Y', strtotime($bill['bill_date'])),
                $bill['bill_number'],
                number_format($bill['total_amount'], 2),
                number_format($bill['paid_amount'], 2),
                number_format($balance, 2)
            ]);
            $totalAmount += $bill['total_amount'];
            $totalPaid += $bill['paid_amount'];
        }

        $excel->addTableRow(['', 'TOTAL', number_format($totalAmount, 2), number_format($totalPaid, 2), number_format($totalAmount - $totalPaid, 2)], true);
        $excel->applyTableBorders($excel->currentRow - count($bills) - 2, $excel->currentRow - 1, 'E');
        $excel->autoSizeColumns('E');

        $excel->download('supplier_statement_' . date('Ymd'));
    }

    // Continue in next comment...
}

    // ====================== TRIAL BALANCE ======================
    public function trialBalance()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Trial Balance',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Trial Balance']
            ]
        ];

        return view('reports/trial_balance', $data);
    }

    public function trialBalancePdf()
    {
        $asOfDate = $this->request->getGet('as_of_date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        $builder = $db->table('journal_entry_lines jel')
            ->select('coa.code, coa.name, SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.code, coa.name')
            ->orderBy('coa.code');
        
        $trialBalanceData = $builder->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Trial Balance');
        $pdf->setReportPeriod('As of ' . date('d/m/Y', strtotime($asOfDate)));

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(25, 7, 'Code', 1, 0, 'C', true);
        $pdf->Cell(85, 7, 'Account Name', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Debit', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Credit', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($trialBalanceData as $row) {
            $pdf->Cell(25, 6, $row['code'], 1, 0, 'L');
            $pdf->Cell(85, 6, $row['name'], 1, 0, 'L');
            $pdf->Cell(40, 6, number_format($row['total_debit'], 2), 1, 0, 'R');
            $pdf->Cell(35, 6, number_format($row['total_credit'], 2), 1, 1, 'R');
            $totalDebit += $row['total_debit'];
            $totalCredit += $row['total_credit'];
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(110, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(40, 7, number_format($totalDebit, 2), 1, 0, 'R');
        $pdf->Cell(35, 7, number_format($totalCredit, 2), 1, 1, 'R');

        $pdf->Output('trial_balance_' . date('Ymd') . '.pdf', 'D');
    }

    public function trialBalanceExcel()
    {
        $asOfDate = $this->request->getGet('as_of_date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        $builder = $db->table('journal_entry_lines jel')
            ->select('coa.code, coa.name, SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.code, coa.name')
            ->orderBy('coa.code');
        
        $trialBalanceData = $builder->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('D');
        $excel->addReportTitle('Trial Balance', 'D');
        $excel->addReportPeriod('As of ' . date('d/m/Y', strtotime($asOfDate)), 'D');

        $headers = ['Code', 'Account Name', 'Debit', 'Credit'];
        $excel->addTableHeader($headers);

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($trialBalanceData as $row) {
            $excel->addTableRow([
                $row['code'],
                $row['name'],
                number_format($row['total_debit'], 2),
                number_format($row['total_credit'], 2)
            ]);
            $totalDebit += $row['total_debit'];
            $totalCredit += $row['total_credit'];
        }

        $excel->addTableRow(['', 'TOTAL', number_format($totalDebit, 2), number_format($totalCredit, 2)], true);
        $excel->applyTableBorders($excel->currentRow - count($trialBalanceData) - 2, $excel->currentRow - 1, 'D');
        $excel->autoSizeColumns('D');

        $excel->download('trial_balance_' . date('Ymd'));
    }

    // ====================== BALANCE SHEET ======================
    public function balanceSheet()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Balance Sheet',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Balance Sheet']
            ]
        ];

        return view('reports/balance_sheet', $data);
    }

    public function balanceSheetPdf()
    {
        $asOfDate = $this->request->getGet('as_of_date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        
        // Get assets
        $assets = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.debit - jel.credit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'asset')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        // Get liabilities
        $liabilities = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.credit - jel.debit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'liability')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        // Get equity
        $equity = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.credit - jel.debit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'equity')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Balance Sheet');
        $pdf->setReportPeriod('As of ' . date('d/m/Y', strtotime($asOfDate)));

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'ASSETS', 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $totalAssets = 0;
        foreach ($assets as $row) {
            $pdf->Cell(130, 6, '  ' . $row['name'], 0, 0, 'L');
            $pdf->Cell(55, 6, number_format($row['balance'], 2), 0, 1, 'R');
            $totalAssets += $row['balance'];
        }
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'Total Assets', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($totalAssets, 2), 1, 1, 'R');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'LIABILITIES', 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $totalLiabilities = 0;
        foreach ($liabilities as $row) {
            $pdf->Cell(130, 6, '  ' . $row['name'], 0, 0, 'L');
            $pdf->Cell(55, 6, number_format($row['balance'], 2), 0, 1, 'R');
            $totalLiabilities += $row['balance'];
        }
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'Total Liabilities', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($totalLiabilities, 2), 1, 1, 'R');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'EQUITY', 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $totalEquity = 0;
        foreach ($equity as $row) {
            $pdf->Cell(130, 6, '  ' . $row['name'], 0, 0, 'L');
            $pdf->Cell(55, 6, number_format($row['balance'], 2), 0, 1, 'R');
            $totalEquity += $row['balance'];
        }
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'Total Equity', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($totalEquity, 2), 1, 1, 'R');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(130, 7, 'TOTAL LIABILITIES & EQUITY', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($totalLiabilities + $totalEquity, 2), 1, 1, 'R');

        $pdf->Output('balance_sheet_' . date('Ymd') . '.pdf', 'D');
    }

    public function balanceSheetExcel()
    {
        $asOfDate = $this->request->getGet('as_of_date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        
        // Get assets
        $assets = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.debit - jel.credit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'asset')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        // Get liabilities
        $liabilities = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.credit - jel.debit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'liability')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        // Get equity
        $equity = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.credit - jel.debit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date <=', $asOfDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'equity')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('B');
        $excel->addReportTitle('Balance Sheet', 'B');
        $excel->addReportPeriod('As of ' . date('d/m/Y', strtotime($asOfDate)), 'B');

        // Assets
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'ASSETS');
        $excel->sheet->getStyle('A' . $excel->currentRow)->getFont()->setBold(true);
        $excel->currentRow++;

        $totalAssets = 0;
        foreach ($assets as $row) {
            $excel->addTableRow(['  ' . $row['name'], number_format($row['balance'], 2)]);
            $totalAssets += $row['balance'];
        }
        $excel->addTableRow(['Total Assets', number_format($totalAssets, 2)], true);
        $excel->currentRow++;

        // Liabilities
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'LIABILITIES');
        $excel->sheet->getStyle('A' . $excel->currentRow)->getFont()->setBold(true);
        $excel->currentRow++;

        $totalLiabilities = 0;
        foreach ($liabilities as $row) {
            $excel->addTableRow(['  ' . $row['name'], number_format($row['balance'], 2)]);
            $totalLiabilities += $row['balance'];
        }
        $excel->addTableRow(['Total Liabilities', number_format($totalLiabilities, 2)], true);
        $excel->currentRow++;

        // Equity
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'EQUITY');
        $excel->sheet->getStyle('A' . $excel->currentRow)->getFont()->setBold(true);
        $excel->currentRow++;

        $totalEquity = 0;
        foreach ($equity as $row) {
            $excel->addTableRow(['  ' . $row['name'], number_format($row['balance'], 2)]);
            $totalEquity += $row['balance'];
        }
        $excel->addTableRow(['Total Equity', number_format($totalEquity, 2)], true);
        $excel->currentRow++;

        $excel->addTableRow(['TOTAL LIABILITIES & EQUITY', number_format($totalLiabilities + $totalEquity, 2)], true);
        $excel->autoSizeColumns('B');

        $excel->download('balance_sheet_' . date('Ymd'));
    }

    // ====================== INCOME STATEMENT ======================
    public function incomeStatement()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Income Statement',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Income Statement']
            ]
        ];

        return view('reports/income_statement', $data);
    }

    public function incomeStatementPdf()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $db = \Config\Database::connect();
        
        // Get revenue
        $revenue = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.credit - jel.debit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date >=', $startDate)
            ->where('je.entry_date <=', $endDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'revenue')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        // Get expenses
        $expenses = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.debit - jel.credit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date >=', $startDate)
            ->where('je.entry_date <=', $endDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'expense')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Income Statement');
        $pdf->setReportPeriod($startDate . ' to ' . $endDate);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'REVENUE', 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $totalRevenue = 0;
        foreach ($revenue as $row) {
            $pdf->Cell(130, 6, '  ' . $row['name'], 0, 0, 'L');
            $pdf->Cell(55, 6, number_format($row['balance'], 2), 0, 1, 'R');
            $totalRevenue += $row['balance'];
        }
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'Total Revenue', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($totalRevenue, 2), 1, 1, 'R');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'EXPENSES', 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $totalExpenses = 0;
        foreach ($expenses as $row) {
            $pdf->Cell(130, 6, '  ' . $row['name'], 0, 0, 'L');
            $pdf->Cell(55, 6, number_format($row['balance'], 2), 0, 1, 'R');
            $totalExpenses += $row['balance'];
        }
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'Total Expenses', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($totalExpenses, 2), 1, 1, 'R');
        $pdf->Ln(3);

        $netIncome = $totalRevenue - $totalExpenses;
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(130, 7, 'NET INCOME', 1, 0, 'L');
        $pdf->Cell(55, 7, number_format($netIncome, 2), 1, 1, 'R');

        $pdf->Output('income_statement_' . date('Ymd') . '.pdf', 'D');
    }

    public function incomeStatementExcel()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $db = \Config\Database::connect();
        
        // Get revenue
        $revenue = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.credit - jel.debit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date >=', $startDate)
            ->where('je.entry_date <=', $endDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'revenue')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        // Get expenses
        $expenses = $db->table('journal_entry_lines jel')
            ->select('coa.name, SUM(jel.debit - jel.credit) as balance')
            ->join('journal_entries je', 'je.id = jel.journal_entry_id')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('je.entry_date >=', $startDate)
            ->where('je.entry_date <=', $endDate)
            ->where('je.status', 'posted')
            ->where('coa.type', 'expense')
            ->where('je.company_id', session()->get('current_company_id'))
            ->groupBy('coa.id, coa.name')
            ->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('B');
        $excel->addReportTitle('Income Statement', 'B');
        $excel->addReportPeriod($startDate . ' to ' . $endDate, 'B');

        // Revenue
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'REVENUE');
        $excel->sheet->getStyle('A' . $excel->currentRow)->getFont()->setBold(true);
        $excel->currentRow++;

        $totalRevenue = 0;
        foreach ($revenue as $row) {
            $excel->addTableRow(['  ' . $row['name'], number_format($row['balance'], 2)]);
            $totalRevenue += $row['balance'];
        }
        $excel->addTableRow(['Total Revenue', number_format($totalRevenue, 2)], true);
        $excel->currentRow++;

        // Expenses
        $excel->sheet->setCellValue('A' . $excel->currentRow, 'EXPENSES');
        $excel->sheet->getStyle('A' . $excel->currentRow)->getFont()->setBold(true);
        $excel->currentRow++;

        $totalExpenses = 0;
        foreach ($expenses as $row) {
            $excel->addTableRow(['  ' . $row['name'], number_format($row['balance'], 2)]);
            $totalExpenses += $row['balance'];
        }
        $excel->addTableRow(['Total Expenses', number_format($totalExpenses, 2)], true);
        $excel->currentRow++;

        $netIncome = $totalRevenue - $totalExpenses;
        $excel->addTableRow(['NET INCOME', number_format($netIncome, 2)], true);
        $excel->autoSizeColumns('B');

        $excel->download('income_statement_' . date('Ymd'));
    }

    // ====================== ATTENDANCE REPORT ======================
    public function attendanceReport()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Attendance Report',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Attendance']
            ],
            'employees' => $this->employeeModel->findAll()
        ];

        return view('reports/attendance_report', $data);
    }

    public function attendanceReportPdf()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $db = \Config\Database::connect();
        $builder = $db->table('attendance a')
            ->select('e.employee_code, e.name, a.attendance_date, a.check_in, a.check_out, a.status')
            ->join('employees e', 'e.id = a.employee_id')
            ->where('a.attendance_date >=', $startDate)
            ->where('a.attendance_date <=', $endDate)
            ->where('a.company_id', session()->get('current_company_id'))
            ->orderBy('a.attendance_date, e.name');
        
        $attendanceData = $builder->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Attendance Report');
        $pdf->setReportPeriod($startDate . ' to ' . $endDate);

        // Table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(25, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Code', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Employee', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Check In', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Check Out', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Status', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        foreach ($attendanceData as $row) {
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['attendance_date'])), 1, 0, 'C');
            $pdf->Cell(25, 6, $row['employee_code'], 1, 0, 'L');
            $pdf->Cell(50, 6, $row['name'], 1, 0, 'L');
            $pdf->Cell(25, 6, $row['check_in'] ?? '-', 1, 0, 'C');
            $pdf->Cell(25, 6, $row['check_out'] ?? '-', 1, 0, 'C');
            $pdf->Cell(35, 6, ucfirst($row['status']), 1, 1, 'C');
        }

        $pdf->Output('attendance_report_' . date('Ymd') . '.pdf', 'D');
    }

    public function attendanceReportExcel()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $db = \Config\Database::connect();
        $builder = $db->table('attendance a')
            ->select('e.employee_code, e.name, a.attendance_date, a.check_in, a.check_out, a.status')
            ->join('employees e', 'e.id = a.employee_id')
            ->where('a.attendance_date >=', $startDate)
            ->where('a.attendance_date <=', $endDate)
            ->where('a.company_id', session()->get('current_company_id'))
            ->orderBy('a.attendance_date, e.name');
        
        $attendanceData = $builder->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('F');
        $excel->addReportTitle('Attendance Report', 'F');
        $excel->addReportPeriod($startDate . ' to ' . $endDate, 'F');

        $headers = ['Date', 'Code', 'Employee', 'Check In', 'Check Out', 'Status'];
        $excel->addTableHeader($headers);

        foreach ($attendanceData as $row) {
            $excel->addTableRow([
                date('d/m/Y', strtotime($row['attendance_date'])),
                $row['employee_code'],
                $row['name'],
                $row['check_in'] ?? '-',
                $row['check_out'] ?? '-',
                ucfirst($row['status'])
            ]);
        }

        $excel->applyTableBorders($excel->currentRow - count($attendanceData) - 1, $excel->currentRow - 1, 'F');
        $excel->autoSizeColumns('F');

        $excel->download('attendance_report_' . date('Ymd'));
    }

    // ====================== PAYROLL REPORT ======================
    public function payrollReport()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Payroll Report',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Payroll']
            ],
            'employees' => $this->employeeModel->findAll()
        ];

        return view('reports/payroll_report', $data);
    }

    public function payrollReportPdf()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');
        
        $db = \Config\Database::connect();
        $builder = $db->table('payrolls p')
            ->select('e.employee_code, e.name, p.period, p.basic_salary, p.allowances, p.deductions, p.net_salary, p.status')
            ->join('employees e', 'e.id = p.employee_id')
            ->where('p.period', $month)
            ->where('p.company_id', session()->get('current_company_id'))
            ->orderBy('e.name');
        
        $payrollData = $builder->get()->getResultArray();

        $pdf = new PdfReportLibrary();
        $pdf->AddPage();
        $pdf->setReportTitle('Payroll Report');
        $pdf->setReportPeriod('Period: ' . date('F Y', strtotime($month . '-01')));

        // Table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(20, 7, 'Code', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'Employee', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Basic', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Allowances', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Deductions', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Net Salary', 1, 1, 'C', true);

        // Table rows
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $totalBasic = 0;
        $totalAllowances = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        foreach ($payrollData as $row) {
            $pdf->Cell(20, 6, $row['employee_code'], 1, 0, 'L');
            $pdf->Cell(45, 6, $row['name'], 1, 0, 'L');
            $pdf->Cell(30, 6, number_format($row['basic_salary'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($row['allowances'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($row['deductions'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($row['net_salary'], 2), 1, 1, 'R');
            $totalBasic += $row['basic_salary'];
            $totalAllowances += $row['allowances'];
            $totalDeductions += $row['deductions'];
            $totalNet += $row['net_salary'];
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(65, 7, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($totalBasic, 2), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($totalAllowances, 2), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($totalDeductions, 2), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($totalNet, 2), 1, 1, 'R');

        $pdf->Output('payroll_report_' . date('Ymd') . '.pdf', 'D');
    }

    public function payrollReportExcel()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');
        
        $db = \Config\Database::connect();
        $builder = $db->table('payrolls p')
            ->select('e.employee_code, e.name, p.period, p.basic_salary, p.allowances, p.deductions, p.net_salary, p.status')
            ->join('employees e', 'e.id = p.employee_id')
            ->where('p.period', $month)
            ->where('p.company_id', session()->get('current_company_id'))
            ->orderBy('e.name');
        
        $payrollData = $builder->get()->getResultArray();

        $excel = new ExcelExportLibrary();
        $excel->addCompanyHeader('F');
        $excel->addReportTitle('Payroll Report', 'F');
        $excel->addReportPeriod('Period: ' . date('F Y', strtotime($month . '-01')), 'F');

        $headers = ['Code', 'Employee', 'Basic Salary', 'Allowances', 'Deductions', 'Net Salary'];
        $excel->addTableHeader($headers);

        $totalBasic = 0;
        $totalAllowances = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        foreach ($payrollData as $row) {
            $excel->addTableRow([
                $row['employee_code'],
                $row['name'],
                number_format($row['basic_salary'], 2),
                number_format($row['allowances'], 2),
                number_format($row['deductions'], 2),
                number_format($row['net_salary'], 2)
            ]);
            $totalBasic += $row['basic_salary'];
            $totalAllowances += $row['allowances'];
            $totalDeductions += $row['deductions'];
            $totalNet += $row['net_salary'];
        }

        $excel->addTableRow([
            '',
            'TOTAL',
            number_format($totalBasic, 2),
            number_format($totalAllowances, 2),
            number_format($totalDeductions, 2),
            number_format($totalNet, 2)
        ], true);

        $excel->applyTableBorders($excel->currentRow - count($payrollData) - 2, $excel->currentRow - 1, 'F');
        $excel->autoSizeColumns('F');

        $excel->download('payroll_report_' . date('Ymd'));
    }
}
