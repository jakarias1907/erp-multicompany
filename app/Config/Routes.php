<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth\LoginController::index');
$routes->get('/login', 'Auth\LoginController::index');
$routes->post('/login/authenticate', 'Auth\LoginController::authenticate');
$routes->get('/logout', 'Auth\LoginController::logout');

// Protected routes
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/dashboard', 'DashboardController::index');
    
    // Master Data Routes
    $routes->group('master', function($routes) {
        // Company Management
        $routes->get('company', 'Master\CompanyController::index');
        $routes->post('company/datatable', 'Master\CompanyController::datatable');
        $routes->get('company/create', 'Master\CompanyController::create');
        $routes->post('company/store', 'Master\CompanyController::store');
        $routes->get('company/edit/(:num)', 'Master\CompanyController::edit/$1');
        $routes->post('company/update/(:num)', 'Master\CompanyController::update/$1');
        $routes->post('company/delete/(:num)', 'Master\CompanyController::delete/$1');
        
        // User Management
        $routes->get('user', 'Master\UserController::index');
        $routes->post('user/datatable', 'Master\UserController::datatable');
        $routes->get('user/create', 'Master\UserController::create');
        $routes->post('user/store', 'Master\UserController::store');
        $routes->get('user/edit/(:num)', 'Master\UserController::edit/$1');
        $routes->post('user/update/(:num)', 'Master\UserController::update/$1');
        $routes->post('user/delete/(:num)', 'Master\UserController::delete/$1');
        $routes->post('user/reset-password/(:num)', 'Master\UserController::resetPassword/$1');
        $routes->post('user/toggle-status/(:num)', 'Master\UserController::toggleStatus/$1');
        $routes->post('user/upload-photo', 'Master\UserController::uploadPhoto');
        
        // Role Management
        $routes->get('role', 'Master\RoleController::index');
        $routes->post('role/datatable', 'Master\RoleController::datatable');
        $routes->get('role/create', 'Master\RoleController::create');
        $routes->post('role/store', 'Master\RoleController::store');
        $routes->get('role/edit/(:num)', 'Master\RoleController::edit/$1');
        $routes->post('role/update/(:num)', 'Master\RoleController::update/$1');
        $routes->post('role/delete/(:num)', 'Master\RoleController::delete/$1');
        $routes->get('role/permissions/(:num)', 'Master\RoleController::permissions/$1');
        $routes->post('role/update-permissions/(:num)', 'Master\RoleController::updatePermissions/$1');
        $routes->post('role/clone/(:num)', 'Master\RoleController::clone/$1');
        
        // Product Management
        $routes->get('product', 'Master\ProductController::index');
        $routes->post('product/datatable', 'Master\ProductController::datatable');
        $routes->get('product/create', 'Master\ProductController::create');
        $routes->post('product/store', 'Master\ProductController::store');
        $routes->get('product/edit/(:num)', 'Master\ProductController::edit/$1');
        $routes->post('product/update/(:num)', 'Master\ProductController::update/$1');
        $routes->post('product/delete/(:num)', 'Master\ProductController::delete/$1');
        
        // Customer Management
        $routes->get('customer', 'Master\CustomerController::index');
        $routes->post('customer/datatable', 'Master\CustomerController::datatable');
        $routes->get('customer/create', 'Master\CustomerController::create');
        $routes->post('customer/store', 'Master\CustomerController::store');
        $routes->get('customer/edit/(:num)', 'Master\CustomerController::edit/$1');
        $routes->post('customer/update/(:num)', 'Master\CustomerController::update/$1');
        $routes->post('customer/delete/(:num)', 'Master\CustomerController::delete/$1');
        $routes->get('customer/statement/(:num)', 'Master\CustomerController::statement/$1');
        $routes->get('customer/outstanding/(:num)', 'Master\CustomerController::getOutstanding/$1');
        $routes->get('customer/export', 'Master\CustomerController::export');
        
        // Supplier Management
        $routes->get('supplier', 'Master\SupplierController::index');
        $routes->post('supplier/datatable', 'Master\SupplierController::datatable');
        $routes->get('supplier/create', 'Master\SupplierController::create');
        $routes->post('supplier/store', 'Master\SupplierController::store');
        $routes->get('supplier/edit/(:num)', 'Master\SupplierController::edit/$1');
        $routes->post('supplier/update/(:num)', 'Master\SupplierController::update/$1');
        $routes->post('supplier/delete/(:num)', 'Master\SupplierController::delete/$1');
        $routes->get('supplier/statement/(:num)', 'Master\SupplierController::statement/$1');
        $routes->get('supplier/export', 'Master\SupplierController::export');
    });
    
    // Finance Routes
    $routes->group('finance', function($routes) {
        // Invoice Management
        $routes->get('invoice', 'Finance\InvoiceController::index');
        $routes->post('invoice/datatable', 'Finance\InvoiceController::datatable');
        $routes->get('invoice/create', 'Finance\InvoiceController::create');
        $routes->post('invoice/store', 'Finance\InvoiceController::store');
        $routes->get('invoice/view/(:num)', 'Finance\InvoiceController::view/$1');
        $routes->get('invoice/print/(:num)', 'Finance\InvoiceController::print/$1');
        $routes->post('invoice/delete/(:num)', 'Finance\InvoiceController::delete/$1');
        
        // Chart of Accounts
        $routes->get('account', 'Finance\ChartOfAccountController::index');
        $routes->get('account/tree-data', 'Finance\ChartOfAccountController::getTreeData');
        $routes->get('account/create', 'Finance\ChartOfAccountController::create');
        $routes->post('account/store', 'Finance\ChartOfAccountController::store');
        $routes->get('account/edit/(:num)', 'Finance\ChartOfAccountController::edit/$1');
        $routes->post('account/update/(:num)', 'Finance\ChartOfAccountController::update/$1');
        $routes->post('account/delete/(:num)', 'Finance\ChartOfAccountController::delete/$1');
        $routes->post('account/toggle-status/(:num)', 'Finance\ChartOfAccountController::toggleStatus/$1');
        
        // Journal Entries
        $routes->get('journal', 'Finance\JournalController::index');
        $routes->post('journal/datatable', 'Finance\JournalController::datatable');
        $routes->get('journal/create', 'Finance\JournalController::create');
        $routes->post('journal/store', 'Finance\JournalController::store');
        $routes->get('journal/view/(:num)', 'Finance\JournalController::view/$1');
        $routes->get('journal/edit/(:num)', 'Finance\JournalController::edit/$1');
        $routes->post('journal/update/(:num)', 'Finance\JournalController::update/$1');
        $routes->post('journal/delete/(:num)', 'Finance\JournalController::delete/$1');
        $routes->post('journal/post/(:num)', 'Finance\JournalController::post/$1');
        $routes->post('journal/approve/(:num)', 'Finance\JournalController::approve/$1');
        
        // Bills (Accounts Payable)
        $routes->get('bill', 'Finance\BillController::index');
        $routes->post('bill/datatable', 'Finance\BillController::datatable');
        $routes->get('bill/create', 'Finance\BillController::create');
        $routes->post('bill/store', 'Finance\BillController::store');
        $routes->get('bill/view/(:num)', 'Finance\BillController::view/$1');
        $routes->get('bill/edit/(:num)', 'Finance\BillController::edit/$1');
        $routes->post('bill/update/(:num)', 'Finance\BillController::update/$1');
        $routes->post('bill/delete/(:num)', 'Finance\BillController::delete/$1');
        
        // Ledger & Reports
        $routes->get('ledger', 'Finance\LedgerController::index');
        $routes->get('ledger/general', 'Finance\LedgerController::generalLedger');
        $routes->get('ledger/trial-balance', 'Finance\LedgerController::trialBalance');
        $routes->get('ledger/balance-sheet', 'Finance\LedgerController::balanceSheet');
        $routes->get('ledger/income-statement', 'Finance\LedgerController::incomeStatement');
        $routes->get('ledger/cash-flow', 'Finance\LedgerController::cashFlow');
    });
    
    // Inventory Routes
    $routes->group('inventory', function($routes) {
        // Warehouse Management
        $routes->get('warehouse', 'Inventory\WarehouseController::index');
        $routes->post('warehouse/datatable', 'Inventory\WarehouseController::datatable');
        $routes->get('warehouse/create', 'Inventory\WarehouseController::create');
        $routes->post('warehouse/store', 'Inventory\WarehouseController::store');
        $routes->get('warehouse/edit/(:num)', 'Inventory\WarehouseController::edit/$1');
        $routes->post('warehouse/update/(:num)', 'Inventory\WarehouseController::update/$1');
        $routes->post('warehouse/delete/(:num)', 'Inventory\WarehouseController::delete/$1');
        
        // Stock Management
        $routes->get('stock', 'Inventory\StockController::index');
        $routes->post('stock/datatable', 'Inventory\StockController::datatable');
        $routes->get('stock/create', 'Inventory\StockController::create');
        $routes->post('stock/store', 'Inventory\StockController::store');
        $routes->get('stock/edit/(:num)', 'Inventory\StockController::edit/$1');
        $routes->post('stock/update/(:num)', 'Inventory\StockController::update/$1');
        $routes->post('stock/delete/(:num)', 'Inventory\StockController::delete/$1');
    });
    
    // Sales Routes
    $routes->group('sales', function($routes) {
        // Quotations
        $routes->get('quotation', 'Sales\QuotationController::index');
        $routes->post('quotation/datatable', 'Sales\QuotationController::datatable');
        $routes->get('quotation/create', 'Sales\QuotationController::create');
        $routes->post('quotation/store', 'Sales\QuotationController::store');
        $routes->get('quotation/edit/(:num)', 'Sales\QuotationController::edit/$1');
        $routes->post('quotation/update/(:num)', 'Sales\QuotationController::update/$1');
        $routes->post('quotation/delete/(:num)', 'Sales\QuotationController::delete/$1');
        
        // Sales Orders
        $routes->get('sales-order', 'Sales\SalesOrderController::index');
        $routes->post('sales-order/datatable', 'Sales\SalesOrderController::datatable');
        $routes->get('sales-order/create', 'Sales\SalesOrderController::create');
        $routes->post('sales-order/store', 'Sales\SalesOrderController::store');
        $routes->get('sales-order/edit/(:num)', 'Sales\SalesOrderController::edit/$1');
        $routes->post('sales-order/update/(:num)', 'Sales\SalesOrderController::update/$1');
        $routes->post('sales-order/delete/(:num)', 'Sales\SalesOrderController::delete/$1');
        
        // Delivery Orders
        $routes->get('delivery', 'Sales\DeliveryOrderController::index');
        $routes->post('delivery/datatable', 'Sales\DeliveryOrderController::datatable');
        $routes->get('delivery/create', 'Sales\DeliveryOrderController::create');
        $routes->post('delivery/store', 'Sales\DeliveryOrderController::store');
        $routes->get('delivery/edit/(:num)', 'Sales\DeliveryOrderController::edit/$1');
        $routes->post('delivery/update/(:num)', 'Sales\DeliveryOrderController::update/$1');
        $routes->post('delivery/delete/(:num)', 'Sales\DeliveryOrderController::delete/$1');
    });
    
    // Purchase Routes
    $routes->group('purchase', function($routes) {
        // Purchase Requests
        $routes->get('pr', 'Purchase\PurchaseRequestController::index');
        $routes->post('pr/datatable', 'Purchase\PurchaseRequestController::datatable');
        $routes->get('pr/create', 'Purchase\PurchaseRequestController::create');
        $routes->post('pr/store', 'Purchase\PurchaseRequestController::store');
        $routes->get('pr/edit/(:num)', 'Purchase\PurchaseRequestController::edit/$1');
        $routes->post('pr/update/(:num)', 'Purchase\PurchaseRequestController::update/$1');
        $routes->post('pr/delete/(:num)', 'Purchase\PurchaseRequestController::delete/$1');
        
        // Purchase Orders
        $routes->get('po', 'Purchase\PurchaseOrderController::index');
        $routes->post('po/datatable', 'Purchase\PurchaseOrderController::datatable');
        $routes->get('po/create', 'Purchase\PurchaseOrderController::create');
        $routes->post('po/store', 'Purchase\PurchaseOrderController::store');
        $routes->get('po/edit/(:num)', 'Purchase\PurchaseOrderController::edit/$1');
        $routes->post('po/update/(:num)', 'Purchase\PurchaseOrderController::update/$1');
        $routes->post('po/delete/(:num)', 'Purchase\PurchaseOrderController::delete/$1');
        
        // Goods Receipts
        $routes->get('gr', 'Purchase\GoodsReceiptController::index');
        $routes->post('gr/datatable', 'Purchase\GoodsReceiptController::datatable');
        $routes->get('gr/create', 'Purchase\GoodsReceiptController::create');
        $routes->post('gr/store', 'Purchase\GoodsReceiptController::store');
        $routes->get('gr/edit/(:num)', 'Purchase\GoodsReceiptController::edit/$1');
        $routes->post('gr/update/(:num)', 'Purchase\GoodsReceiptController::update/$1');
        $routes->post('gr/delete/(:num)', 'Purchase\GoodsReceiptController::delete/$1');
    });
    
    // HR Routes
    $routes->group('hr', function($routes) {
        // Employee Management
        $routes->get('employee', 'HR\EmployeeController::index');
        $routes->post('employee/datatable', 'HR\EmployeeController::datatable');
        $routes->get('employee/create', 'HR\EmployeeController::create');
        $routes->post('employee/store', 'HR\EmployeeController::store');
        $routes->get('employee/edit/(:num)', 'HR\EmployeeController::edit/$1');
        $routes->post('employee/update/(:num)', 'HR\EmployeeController::update/$1');
        $routes->post('employee/delete/(:num)', 'HR\EmployeeController::delete/$1');
        
        // Attendance
        $routes->get('attendance', 'HR\AttendanceController::index');
        $routes->post('attendance/datatable', 'HR\AttendanceController::datatable');
        $routes->get('attendance/create', 'HR\AttendanceController::create');
        $routes->post('attendance/store', 'HR\AttendanceController::store');
        $routes->get('attendance/edit/(:num)', 'HR\AttendanceController::edit/$1');
        $routes->post('attendance/update/(:num)', 'HR\AttendanceController::update/$1');
        $routes->post('attendance/delete/(:num)', 'HR\AttendanceController::delete/$1');
        
        // Leave Management
        $routes->get('leave', 'HR\LeaveController::index');
        $routes->post('leave/datatable', 'HR\LeaveController::datatable');
        $routes->get('leave/create', 'HR\LeaveController::create');
        $routes->post('leave/store', 'HR\LeaveController::store');
        $routes->get('leave/edit/(:num)', 'HR\LeaveController::edit/$1');
        $routes->post('leave/update/(:num)', 'HR\LeaveController::update/$1');
        $routes->post('leave/delete/(:num)', 'HR\LeaveController::delete/$1');
        
        // Payroll
        $routes->get('payroll', 'HR\PayrollController::index');
        $routes->post('payroll/datatable', 'HR\PayrollController::datatable');
        $routes->get('payroll/create', 'HR\PayrollController::create');
        $routes->post('payroll/store', 'HR\PayrollController::store');
        $routes->get('payroll/edit/(:num)', 'HR\PayrollController::edit/$1');
        $routes->post('payroll/update/(:num)', 'HR\PayrollController::update/$1');
        $routes->post('payroll/delete/(:num)', 'HR\PayrollController::delete/$1');
    });
    
    // Reports Routes
    $routes->group('reports', function($routes) {
        $routes->get('/', 'ReportController::index');
        
        // Sales Report
        $routes->get('sales-report', 'ReportController::salesReport');
        $routes->get('sales-report-pdf', 'ReportController::salesReportPdf');
        $routes->get('sales-report-excel', 'ReportController::salesReportExcel');
        
        // Purchase Report
        $routes->get('purchase-report', 'ReportController::purchaseReport');
        $routes->get('purchase-report-pdf', 'ReportController::purchaseReportPdf');
        $routes->get('purchase-report-excel', 'ReportController::purchaseReportExcel');
        
        // Inventory Report
        $routes->get('inventory-report', 'ReportController::inventoryReport');
        $routes->get('inventory-report-pdf', 'ReportController::inventoryReportPdf');
        $routes->get('inventory-report-excel', 'ReportController::inventoryReportExcel');
        
        // Customer Statement
        $routes->get('customer-statement', 'ReportController::customerStatement');
        $routes->get('customer-statement-pdf', 'ReportController::customerStatementPdf');
        $routes->get('customer-statement-excel', 'ReportController::customerStatementExcel');
        
        // Supplier Statement
        $routes->get('supplier-statement', 'ReportController::supplierStatement');
        $routes->get('supplier-statement-pdf', 'ReportController::supplierStatementPdf');
        $routes->get('supplier-statement-excel', 'ReportController::supplierStatementExcel');
        
        // Trial Balance
        $routes->get('trial-balance', 'ReportController::trialBalance');
        $routes->get('trial-balance-pdf', 'ReportController::trialBalancePdf');
        $routes->get('trial-balance-excel', 'ReportController::trialBalanceExcel');
        
        // Balance Sheet
        $routes->get('balance-sheet', 'ReportController::balanceSheet');
        $routes->get('balance-sheet-pdf', 'ReportController::balanceSheetPdf');
        $routes->get('balance-sheet-excel', 'ReportController::balanceSheetExcel');
        
        // Income Statement
        $routes->get('income-statement', 'ReportController::incomeStatement');
        $routes->get('income-statement-pdf', 'ReportController::incomeStatementPdf');
        $routes->get('income-statement-excel', 'ReportController::incomeStatementExcel');
        
        // Attendance Report
        $routes->get('attendance-report', 'ReportController::attendanceReport');
        $routes->get('attendance-report-pdf', 'ReportController::attendanceReportPdf');
        $routes->get('attendance-report-excel', 'ReportController::attendanceReportExcel');
        
        // Payroll Report
        $routes->get('payroll-report', 'ReportController::payrollReport');
        $routes->get('payroll-report-pdf', 'ReportController::payrollReportPdf');
        $routes->get('payroll-report-excel', 'ReportController::payrollReportExcel');
    });
});
