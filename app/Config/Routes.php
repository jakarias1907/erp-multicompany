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
});
