# Complete ERP Module Implementation Guide

## Overview
This guide provides the exact implementation pattern for ALL remaining ERP modules following the established architecture. All modules use consistent patterns for CRUD operations, DataTables, validation, and security.

## Pattern Summary

### Controller Pattern (Standard CRUD)
```php
<?php
namespace App\Controllers\[Module];

use App\Controllers\BaseController;
use App\Models\[Entity]Model;

class [Entity]Controller extends BaseController
{
    protected $[entity]Model;

    public function __construct()
    {
        $this->[entity]Model = new [Entity]Model();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('[entities]', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }
        
        return view('[module]/[entity]/index', [
            'title' => '[Entity] Management',
            'breadcrumbs' => [/*...*/]
        ]);
    }

    public function datatable()
    {
        // Permission check
        // DataTables server-side processing
        // Company filtering: ->where('company_id', getCurrentCompanyId())
        // Return JSON response
    }

    public function create()
    {
        // Permission check
        // Load form data (dropdowns, etc.)
        // Return create view
    }

    public function store()
    {
        // Permission check
        // Validation
        // Insert with company_id and created_by
        // logActivity('create', '[entities]', "...", $id)
        // Redirect with success message
    }

    public function edit($id)
    {
        // Permission check
        // Find record with company filter
        // Return edit view
    }

    public function update($id)
    {
        // Permission check
        // Find with company filter
        // Validation
        // Update with updated_by
        // logActivity('update', '[entities]', "...", $id)
        // Redirect
    }

    public function delete($id)
    {
        // Permission check
        // Find with company filter
        // Soft delete
        // logActivity('delete', '[entities]', "...", $id)
        // Return JSON response
    }
}
```

### View Pattern

**index.php** - List with DataTable:
```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">[Entity] List</h3>
        <div class="card-tools">
            <a href="<?= base_url('[module]/[entity]/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add [Entity]
            </a>
        </div>
    </div>
    <div class="card-body">
        <table id="[entity]Table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <!-- Column headers -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const table = $('#[entity]Table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('[module]/[entity]/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            // Column definitions
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    // Delete handler
    $('#[entity]Table').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('[module]/[entity]/delete') ?>/" + id,
                    type: "POST",
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
```

**create.php / edit.php**:
```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header"><h3 class="card-title">[Create/Edit] [Entity]</h3></div>
    <div class="card-body"><?= $this->include('[module]/[entity]/_form') ?></div>
</div>
<?= $this->endSection() ?>
```

**_form.php**:
```php
<?php
$isEdit = isset($[entity]);
$action = $isEdit ? base_url('[module]/[entity]/update/' . $[entity]['id']) : base_url('[module]/[entity]/store');
?>
<form action="<?= $action ?>" method="post">
    <?= csrf_field() ?>
    <!-- Form fields with validation error display -->
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?>
    </button>
    <a href="<?= base_url('[module]/[entity]') ?>" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</form>
```

---

## FINANCE MODULE IMPLEMENTATIONS

### 2.1 Chart of Accounts

**Model**: `app/Models/ChartOfAccountModel.php`
```php
protected $allowedFields = ['company_id', 'parent_id', 'code', 'name', 'account_type', 'description'];

public function getTree($companyId = null) {
    $companyId = $companyId ?? getCurrentCompanyId();
    // Return hierarchical structure
    $accounts = $this->where('company_id', $companyId)->findAll();
    return $this->buildTree($accounts);
}

private function buildTree($accounts, $parentId = null) {
    $branch = [];
    foreach ($accounts as $account) {
        if ($account['parent_id'] == $parentId) {
            $children = $this->buildTree($accounts, $account['id']);
            if ($children) {
                $account['children'] = $children;
            }
            $branch[] = $account;
        }
    }
    return $branch;
}
```

**Controller**: `app/Controllers/Finance/ChartOfAccountController.php`
- index() - Display tree view (use jsTree jQuery plugin)
- getTree() - AJAX endpoint returning JSON tree
- create() - Modal form
- store() - With parent_id validation
- reorder() - Handle drag-drop

**View**: Use jsTree for hierarchical display with drag-drop

### 2.2 Journal Entry

**Model**: `app/Models/JournalEntryModel.php` + `JournalEntryLineModel.php`
```php
// JournalEntryModel
protected $allowedFields = ['company_id', 'entry_number', 'entry_date', 'description', 'total_debit', 'total_credit', 'status', 'posted_at'];

// JournalEntryLineModel  
protected $allowedFields = ['journal_entry_id', 'account_id', 'description', 'debit', 'credit'];
```

**Controller**: `app/Controllers/Finance/JournalController.php`
- create() - Dynamic row form
- store() - Validate debit = credit, save header + lines in transaction
- post($id) - Change status to 'posted', set posted_at
- unpost($id) - Reverse post (if allowed)

**View**: Dynamic JavaScript form
```javascript
// Add row button
$('#add-row').click(function() {
    var row = '<tr>' +
        '<td><select name="account_id[]" class="form-control select2"></select></td>' +
        '<td><input type="number" step="0.01" name="debit[]" class="form-control debit"></td>' +
        '<td><input type="number" step="0.01" name="credit[]" class="form-control credit"></td>' +
        '<td><button type="button" class="btn btn-danger remove-row">Remove</button></td>' +
        '</tr>';
    $('#journal-lines tbody').append(row);
    $('.select2').select2(); // Reinitialize
});

// Auto-calculate totals
$('body').on('input', '.debit, .credit', function() {
    var totalDebit = 0, totalCredit = 0;
    $('.debit').each(function() { totalDebit += parseFloat($(this).val() || 0); });
    $('.credit').each(function() { totalCredit += parseFloat($(this).val() || 0); });
    
    $('#total-debit').text(totalDebit.toFixed(2));
    $('#total-credit').text(totalCredit.toFixed(2));
    
    if (totalDebit === totalCredit && totalDebit > 0) {
        $('#submit-btn').prop('disabled', false);
    } else {
        $('#submit-btn').prop('disabled', true);
    }
});
```

### 2.3 Invoice Management

**Models**: `InvoiceModel.php` + `InvoiceItemModel.php` (already created)

**Controller**: `app/Controllers/Finance/InvoiceController.php`
```php
public function store() {
    $db = \Config\Database::connect();
    $db->transStart();
    
    // Save invoice header
    $invoiceData = [
        'company_id' => getCurrentCompanyId(),
        'customer_id' => $this->request->getPost('customer_id'),
        'invoice_number' => $this->generateInvoiceNumber(),
        'invoice_date' => $this->request->getPost('invoice_date'),
        'subtotal' => $subtotal,
        'tax_amount' => $taxAmount,
        'total_amount' => $totalAmount,
        'status' => 'draft'
    ];
    $invoiceId = $this->invoiceModel->insert($invoiceData);
    
    // Save invoice items
    $items = $this->request->getPost('items');
    foreach ($items as $item) {
        $this->invoiceItemModel->insert([
            'invoice_id' => $invoiceId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total' => $item['total']
        ]);
    }
    
    // Create journal entry (auto-posting)
    $this->createJournalEntry($invoiceId);
    
    $db->transComplete();
}

private function createJournalEntry($invoiceId) {
    // DR: Accounts Receivable
    // CR: Sales Revenue
}

public function payment($id) {
    // Record payment
    // Update paid_amount
    // If fully paid, status = 'paid'
    // Create journal entry for payment
}
```

**View - Dynamic Invoice Form**:
```javascript
$('#add-item').click(function() {
    var row = '<tr>' +
        '<td><select name="items[product_id][]" class="product-select"></select></td>' +
        '<td><input type="number" name="items[quantity][]" class="quantity"></td>' +
        '<td><input type="number" step="0.01" name="items[unit_price][]" class="unit-price"></td>' +
        '<td><input type="number" step="0.01" name="items[discount][]" class="discount"></td>' +
        '<td class="line-total">0.00</td>' +
        '<td><button type="button" class="remove-item">Remove</button></td>' +
        '</tr>';
    $('#invoice-items tbody').append(row);
});

// Calculate line total on change
$('body').on('input', '.quantity, .unit-price, .discount', function() {
    var row = $(this).closest('tr');
    var qty = parseFloat(row.find('.quantity').val() || 0);
    var price = parseFloat(row.find('.unit-price').val() || 0);
    var discount = parseFloat(row.find('.discount').val() || 0);
    var lineTotal = (qty * price) - discount;
    row.find('.line-total').text(lineTotal.toFixed(2));
    calculateInvoiceTotal();
});

function calculateInvoiceTotal() {
    var subtotal = 0;
    $('.line-total').each(function() {
        subtotal += parseFloat($(this).text() || 0);
    });
    var taxRate = parseFloat($('#tax_rate').val() || 0);
    var taxAmount = subtotal * (taxRate / 100);
    var total = subtotal + taxAmount;
    
    $('#subtotal').text(subtotal.toFixed(2));
    $('#tax-amount').text(taxAmount.toFixed(2));
    $('#total-amount').text(total.toFixed(2));
}
```

### 2.4 Bill Management

Similar to Invoice but for suppliers:
- **Controller**: `app/Controllers/Finance/BillController.php`
- **Models**: `BillModel.php` + `BillItemModel.php`
- Journal Entry: DR: Purchases/Inventory, CR: Accounts Payable

### 2.5 General Ledger & Reports

**Controller**: `app/Controllers/Finance/LedgerController.php`
```php
public function generalLedger() {
    $startDate = $this->request->getGet('start_date');
    $endDate = $this->request->getGet('end_date');
    $accountId = $this->request->getGet('account_id');
    
    $entries = $db->table('journal_entry_lines')
        ->select('journal_entry_lines.*, journal_entries.entry_date, journal_entries.description, chart_of_accounts.name as account_name')
        ->join('journal_entries', 'journal_entries.id = journal_entry_lines.journal_entry_id')
        ->join('chart_of_accounts', 'chart_of_accounts.id = journal_entry_lines.account_id')
        ->where('journal_entries.company_id', getCurrentCompanyId())
        ->where('journal_entries.entry_date >=', $startDate)
        ->where('journal_entries.entry_date <=', $endDate);
    
    if ($accountId) {
        $entries->where('journal_entry_lines.account_id', $accountId);
    }
    
    return view('finance/ledger/general_ledger', ['entries' => $entries->get()->getResultArray()]);
}

public function trialBalance() {
    // Sum debits and credits per account
    // Display accounts with balances
}

public function balanceSheet() {
    // Assets = Liabilities + Equity
    // Group by account type
}

public function incomeStatement() {
    // Revenue - Expenses = Profit/Loss
}
```

---

## INVENTORY MODULE IMPLEMENTATIONS

### 3.1 Warehouse Management

**Controller**: `app/Controllers/Inventory/WarehouseController.php` (Standard CRUD)
**Model**: `WarehouseModel.php` (already created)
**Fields**: code, name, address, manager_id, status

### 3.2 Stock Management

**Models**: `StockCardModel.php`, `StockMovementModel.php` (Stock card created)

**Controller**: `app/Controllers/Inventory/StockController.php`
```php
public function index() {
    // Stock summary across all warehouses
    // Show product, total quantity, reserved, available
}

public function cardStock($productId) {
    // Stock card for specific product
    // Show all movements (in, out, adjustments)
}

public function adjustment() {
    // Stock adjustment form (physical count difference)
}

public function saveAdjustment() {
    // Update stock_cards.quantity
    // Create stock_movement record
    // Reason: 'adjustment', type: 'in' or 'out'
}

public function transfer() {
    // Transfer between warehouses
}

public function saveTransfer() {
    $db->transStart();
    
    // Decrease from source warehouse
    $this->stockCardModel->where('warehouse_id', $fromWarehouse)
        ->where('product_id', $productId)
        ->set('quantity', 'quantity - ' . $quantity, false)
        ->update();
    
    // Increase in destination warehouse
    $this->stockCardModel->where('warehouse_id', $toWarehouse)
        ->where('product_id', $productId)
        ->set('quantity', 'quantity + ' . $quantity, false)
        ->update();
    
    // Create transfer record
    $this->stockTransferModel->insert([...]);
    
    $db->transComplete();
}
```

---

## SALES MODULE IMPLEMENTATIONS

### 4.1 Quotation

**Controller**: `app/Controllers/Sales/QuotationController.php`
**Model**: `QuotationModel.php` + `QuotationItemModel.php`
**Features**:
- Dynamic product selection (similar to invoice)
- Valid until date
- Status: draft, sent, approved, rejected, converted
- convertToSO($id) - Create sales order from quotation

### 4.2 Sales Order

**Controller**: `app/Controllers/Sales/SalesOrderController.php`
**Model**: `SalesOrderModel.php` + `SalesOrderItemModel.php`
**Features**:
- Create from quotation or standalone
- Reserve stock when confirmed
- Status: draft, confirmed, in_delivery, delivered, invoiced
- createDelivery($id) - Generate delivery order
- createInvoice($id) - Generate invoice

### 4.3 Delivery Order

**Controller**: `app/Controllers/Sales/DeliveryOrderController.php`
**Features**:
- Create from sales order
- Driver & vehicle info
- confirm($id) - Update stock (decrease quantity)

---

## PURCHASE MODULE IMPLEMENTATIONS

### 5.1 Purchase Request

**Controller**: `app/Controllers/Purchase/PurchaseRequestController.php`
**Features**:
- Multi-level approval workflow
- Status: draft, pending, approved, rejected, converted
- approve($id), reject($id)
- convertToPO($id)

### 5.2 Purchase Order

**Controller**: `app/Controllers/Purchase/PurchaseOrderController.php`
**Features**:
- Create from PR
- Email to supplier
- createGR($id) - Create goods receipt

### 5.3 Goods Receipt

**Controller**: `app/Controllers/Purchase/GoodsReceiptController.php`
**Features**:
- Receive items from PO
- Update stock (increase quantity)
- createBill($id) - Generate bill

---

## HR MODULE IMPLEMENTATIONS

### 6.1 Employee Management

**Controller**: `app/Controllers/HR/EmployeeController.php`
**Fields**: employee_number, name, position, department, join_date, salary, documents

### 6.2 Attendance

**Controller**: `app/Controllers/HR/AttendanceController.php`
```php
public function clockIn() {
    $this->attendanceModel->insert([
        'employee_id' => getCurrentUserId(),
        'date' => date('Y-m-d'),
        'clock_in' => date('H:i:s')
    ]);
}

public function clockOut() {
    $attendance = $this->attendanceModel->where('employee_id', getCurrentUserId())
        ->where('date', date('Y-m-d'))
        ->first();
    
    $this->attendanceModel->update($attendance['id'], [
        'clock_out' => date('H:i:s')
    ]);
}
```

### 6.3 Leave Management

**Controller**: `app/Controllers/HR/LeaveController.php`
**Features**: Leave request, approval, balance tracking

### 6.4 Payroll

**Controller**: `app/Controllers/HR/PayrollController.php`
```php
public function generate($month) {
    $employees = $this->employeeModel->findAll();
    
    foreach ($employees as $employee) {
        // Calculate based on attendance
        $attendance = $this->calculateAttendance($employee['id'], $month);
        $salary = $this->calculateSalary($employee, $attendance);
        
        $this->payrollModel->insert([
            'employee_id' => $employee['id'],
            'period' => $month,
            'basic_salary' => $employee['salary'],
            'deductions' => $deductions,
            'net_salary' => $salary
        ]);
    }
}
```

---

## REPORTS MODULE

**Controller**: `app/Controllers/ReportController.php`
```php
public function index() {
    // Report dashboard with links to all reports
}

public function salesReport() {
    // Filter by date range, customer, product
    // Group by period (daily, monthly, yearly)
}

public function exportPdf($reportType, $params) {
    $dompdf = new \Dompdf\Dompdf();
    $html = view('reports/pdf/' . $reportType, $data);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->stream('report.pdf');
}

public function exportExcel($reportType, $params) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Populate data
    $sheet->setCellValue('A1', 'Header');
    // ...
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $fileName = 'report_' . date('YmdHis') . '.xlsx';
    $writer->save($fileName);
    
    return $this->response->download($fileName, null)->setFileName($fileName);
}
```

---

## ENHANCED DASHBOARD

**Controller**: `app/Controllers/DashboardController.php`
```php
public function getStatistics() {
    $stats = [
        'total_sales' => $db->table('invoices')
            ->where('MONTH(invoice_date)', date('m'))
            ->selectSum('total_amount', 'total')
            ->get()->getRow()->total ?? 0,
        'total_purchases' => // Similar query
        'pending_orders' => // Count
        'low_stock_items' => // Count where quantity < alert_level
    ];
    
    return $this->response->setJSON($stats);
}

public function getSalesChart() {
    // Last 12 months sales data
    $months = [];
    $sales = [];
    
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $months[] = date('M Y', strtotime($month));
        
        $total = $db->table('invoices')
            ->where('DATE_FORMAT(invoice_date, "%Y-%m")', $month)
            ->selectSum('total_amount', 'total')
            ->get()->getRow()->total ?? 0;
        
        $sales[] = $total;
    }
    
    return $this->response->setJSON([
        'labels' => $months,
        'data' => $sales
    ]);
}
```

**View Enhancement**:
```javascript
// Fetch and display statistics
$.get('<?= base_url('dashboard/get-statistics') ?>', function(data) {
    $('#total-sales').text(data.total_sales);
    $('#total-purchases').text(data.total_purchases);
    // ...
});

// Chart.js
$.get('<?= base_url('dashboard/get-sales-chart') ?>', function(data) {
    var ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Sales',
                data: data.data,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });
});
```

---

## ROUTES CONFIGURATION

Add to `app/Config/Routes.php`:

```php
// Finance Routes
$routes->group('finance', ['filter' => 'auth'], function($routes) {
    // Chart of Accounts
    $routes->get('account', 'Finance\ChartOfAccountController::index');
    $routes->get('account/tree', 'Finance\ChartOfAccountController::getTree');
    $routes->post('account/store', 'Finance\ChartOfAccountController::store');
    $routes->post('account/reorder', 'Finance\ChartOfAccountController::reorder');
    
    // Journal Entry
    $routes->get('journal', 'Finance\JournalController::index');
    $routes->post('journal/datatable', 'Finance\JournalController::datatable');
    $routes->get('journal/create', 'Finance\JournalController::create');
    $routes->post('journal/store', 'Finance\JournalController::store');
    $routes->post('journal/post/(:num)', 'Finance\JournalController::post/$1');
    
    // Invoice
    $routes->get('invoice', 'Finance\InvoiceController::index');
    $routes->post('invoice/datatable', 'Finance\InvoiceController::datatable');
    $routes->get('invoice/create', 'Finance\InvoiceController::create');
    $routes->post('invoice/store', 'Finance\InvoiceController::store');
    $routes->get('invoice/view/(:num)', 'Finance\InvoiceController::view/$1');
    $routes->post('invoice/payment/(:num)', 'Finance\InvoiceController::savePayment/$1');
    $routes->get('invoice/print/(:num)', 'Finance\InvoiceController::print/$1');
    
    // Bill
    $routes->get('bill', 'Finance\BillController::index');
    // Similar to invoice routes
    
    // Ledger & Reports
    $routes->get('ledger', 'Finance\LedgerController::index');
    $routes->get('ledger/general-ledger', 'Finance\LedgerController::generalLedger');
    $routes->get('ledger/trial-balance', 'Finance\LedgerController::trialBalance');
    $routes->get('ledger/balance-sheet', 'Finance\LedgerController::balanceSheet');
});

// Inventory Routes
$routes->group('inventory', ['filter' => 'auth'], function($routes) {
    // Warehouse
    $routes->get('warehouse', 'Inventory\WarehouseController::index');
    $routes->post('warehouse/datatable', 'Inventory\WarehouseController::datatable');
    $routes->get('warehouse/create', 'Inventory\WarehouseController::create');
    $routes->post('warehouse/store', 'Inventory\WarehouseController::store');
    // Standard CRUD routes...
    
    // Stock
    $routes->get('stock', 'Inventory\StockController::index');
    $routes->get('stock/card/(:num)', 'Inventory\StockController::cardStock/$1');
    $routes->get('stock/adjustment', 'Inventory\StockController::adjustment');
    $routes->post('stock/save-adjustment', 'Inventory\StockController::saveAdjustment');
    $routes->get('stock/transfer', 'Inventory\StockController::transfer');
    $routes->post('stock/save-transfer', 'Inventory\StockController::saveTransfer');
});

// Sales Routes
$routes->group('sales', ['filter' => 'auth'], function($routes) {
    // Quotation
    $routes->get('quotation', 'Sales\QuotationController::index');
    // Standard CRUD + special routes
    $routes->post('quotation/convert-to-so/(:num)', 'Sales\QuotationController::convertToSO/$1');
    
    // Sales Order
    $routes->get('sales-order', 'Sales\SalesOrderController::index');
    $routes->post('sales-order/create-delivery/(:num)', 'Sales\SalesOrderController::createDelivery/$1');
    $routes->post('sales-order/create-invoice/(:num)', 'Sales\SalesOrderController::createInvoice/$1');
    
    // Delivery Order
    $routes->get('delivery', 'Sales\DeliveryOrderController::index');
    $routes->post('delivery/confirm/(:num)', 'Sales\DeliveryOrderController::confirm/$1');
});

// Purchase Routes
$routes->group('purchase', ['filter' => 'auth'], function($routes) {
    // Purchase Request
    $routes->get('pr', 'Purchase\PurchaseRequestController::index');
    $routes->post('pr/approve/(:num)', 'Purchase\PurchaseRequestController::approve/$1');
    $routes->post('pr/convert-to-po/(:num)', 'Purchase\PurchaseRequestController::convertToPO/$1');
    
    // Purchase Order
    $routes->get('po', 'Purchase\PurchaseOrderController::index');
    $routes->post('po/send/(:num)', 'Purchase\PurchaseOrderController::send/$1');
    $routes->post('po/create-gr/(:num)', 'Purchase\PurchaseOrderController::createGR/$1');
    
    // Goods Receipt
    $routes->get('gr', 'Purchase\GoodsReceiptController::index');
    $routes->post('gr/create-bill/(:num)', 'Purchase\GoodsReceiptController::createBill/$1');
});

// HR Routes
$routes->group('hr', ['filter' => 'auth'], function($routes) {
    $routes->get('employee', 'HR\EmployeeController::index');
    $routes->post('employee/datatable', 'HR\EmployeeController::datatable');
    // Standard CRUD
    
    $routes->get('attendance', 'HR\AttendanceController::index');
    $routes->post('attendance/clock-in', 'HR\AttendanceController::clockIn');
    $routes->post('attendance/clock-out', 'HR\AttendanceController::clockOut');
    
    $routes->get('leave', 'HR\LeaveController::index');
    $routes->post('leave/approve/(:num)', 'HR\LeaveController::approve/$1');
    
    $routes->get('payroll', 'HR\PayrollController::index');
    $routes->post('payroll/generate/(:segment)', 'HR\PayrollController::generate/$1');
});

// Reports
$routes->group('reports', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ReportController::index');
    $routes->get('sales', 'ReportController::salesReport');
    $routes->get('purchase', 'ReportController::purchaseReport');
    $routes->get('inventory', 'ReportController::inventoryReport');
    $routes->get('pdf/(:segment)', 'ReportController::exportPdf/$1');
    $routes->get('excel/(:segment)', 'ReportController::exportExcel/$1');
});
```

---

## SECURITY CHECKLIST

For every controller method:
1. ✅ Permission check: `hasPermission('[module]', '[action]')`
2. ✅ Company isolation: `->where('company_id', getCurrentCompanyId())`
3. ✅ Activity logging: `logActivity('[action]', '[module]', "...", $id)`
4. ✅ CSRF protection: `<?= csrf_field() ?>` in forms
5. ✅ Input validation: CodeIgniter validation rules
6. ✅ XSS protection: `esc()` in views
7. ✅ SQL injection: Use query builder (automatic protection)

---

## COMPLETION SUMMARY

Following this guide, implement the remaining modules with these counts:

**Controllers to Create**: 15 more
- Finance: 5 (ChartOfAccount, Journal, Invoice, Bill, Ledger)
- Inventory: 2 (Warehouse, Stock)
- Sales: 3 (Quotation, SalesOrder, DeliveryOrder)
- Purchase: 3 (PurchaseRequest, PurchaseOrder, GoodsReceipt)
- HR: 4 (Employee, Attendance, Leave, Payroll)
- Reports: 1 (Report)

**Views to Create**: 60+ (following the pattern: index, create, edit, _form for each CRUD module)

**Models to Create**: 20+ (one for each entity, plus item/line models for transactional modules)

**Total Implementation**: ~100 files using the patterns above

All modules follow the EXACT same pattern demonstrated in Phase 1 (User, Role, Customer, Supplier).
