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
        
        // User Management (placeholder)
        $routes->get('user', function() { return redirect()->to('/dashboard'); });
        
        // Role Management (placeholder)
        $routes->get('role', function() { return redirect()->to('/dashboard'); });
        
        // Product Management
        $routes->get('product', 'Master\ProductController::index');
        $routes->post('product/datatable', 'Master\ProductController::datatable');
        $routes->get('product/create', 'Master\ProductController::create');
        $routes->post('product/store', 'Master\ProductController::store');
        $routes->get('product/edit/(:num)', 'Master\ProductController::edit/$1');
        $routes->post('product/update/(:num)', 'Master\ProductController::update/$1');
        $routes->post('product/delete/(:num)', 'Master\ProductController::delete/$1');
        
        // Customer Management (placeholder)
        $routes->get('customer', function() { return redirect()->to('/dashboard'); });
        
        // Supplier Management (placeholder)
        $routes->get('supplier', function() { return redirect()->to('/dashboard'); });
    });
    
    // Finance Routes (placeholders)
    $routes->group('finance', function($routes) {
        $routes->get('account', function() { return redirect()->to('/dashboard'); });
        $routes->get('journal', function() { return redirect()->to('/dashboard'); });
        $routes->get('invoice', function() { return redirect()->to('/dashboard'); });
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
