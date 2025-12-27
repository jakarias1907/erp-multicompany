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
    
    // Finance Routes (placeholders)
    $routes->group('finance', function($routes) {
        // Invoice Management
        $routes->get('invoice', 'Finance\InvoiceController::index');
        $routes->post('invoice/datatable', 'Finance\InvoiceController::datatable');
        $routes->get('invoice/create', 'Finance\InvoiceController::create');
        $routes->post('invoice/store', 'Finance\InvoiceController::store');
        $routes->get('invoice/view/(:num)', 'Finance\InvoiceController::view/$1');
        $routes->get('invoice/print/(:num)', 'Finance\InvoiceController::print/$1');
        $routes->post('invoice/delete/(:num)', 'Finance\InvoiceController::delete/$1');
        
        // Placeholders for other finance modules
        $routes->get('account', function() { return redirect()->to('/dashboard'); });
        $routes->get('journal', function() { return redirect()->to('/dashboard'); });
        $routes->get('bill', function() { return redirect()->to('/dashboard'); });
        $routes->get('ledger', function() { return redirect()->to('/dashboard'); });
    });
    
    // Inventory Routes (placeholders)
    $routes->group('inventory', function($routes) {
        $routes->get('warehouse', function() { return redirect()->to('/dashboard'); });
        $routes->get('stock', function() { return redirect()->to('/dashboard'); });
        $routes->get('stock/movements', function() { return redirect()->to('/dashboard'); });
        $routes->get('stock/adjustment', function() { return redirect()->to('/dashboard'); });
        $routes->get('stock/transfer', function() { return redirect()->to('/dashboard'); });
    });
    
    // Sales Routes (placeholders)
    $routes->group('sales', function($routes) {
        $routes->get('quotation', function() { return redirect()->to('/dashboard'); });
        $routes->get('sales-order', function() { return redirect()->to('/dashboard'); });
        $routes->get('delivery', function() { return redirect()->to('/dashboard'); });
    });
    
    // Purchase Routes (placeholders)
    $routes->group('purchase', function($routes) {
        $routes->get('pr', function() { return redirect()->to('/dashboard'); });
        $routes->get('po', function() { return redirect()->to('/dashboard'); });
        $routes->get('gr', function() { return redirect()->to('/dashboard'); });
    });
    
    // HR Routes (placeholders)
    $routes->group('hr', function($routes) {
        $routes->get('employee', function() { return redirect()->to('/dashboard'); });
        $routes->get('attendance', function() { return redirect()->to('/dashboard'); });
        $routes->get('leave', function() { return redirect()->to('/dashboard'); });
        $routes->get('payroll', function() { return redirect()->to('/dashboard'); });
    });
    
    // Reports Routes (placeholders)
    $routes->group('reports', function($routes) {
        $routes->get('/', function() { return redirect()->to('/dashboard'); });
        $routes->get('sales', function() { return redirect()->to('/dashboard'); });
        $routes->get('purchase', function() { return redirect()->to('/dashboard'); });
        $routes->get('inventory', function() { return redirect()->to('/dashboard'); });
    });
});
