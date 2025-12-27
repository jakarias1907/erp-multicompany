<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
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

    public function sales()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Sales Reports',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Sales']
            ]
        ];

        return view('reports/sales', $data);
    }

    public function purchase()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Purchase Reports',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Purchase']
            ]
        ];

        return view('reports/purchase', $data);
    }

    public function inventory()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Inventory Reports',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => base_url('reports')],
                ['label' => 'Inventory']
            ]
        ];

        return view('reports/inventory', $data);
    }

    public function customerStatement($customerId = null)
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
            'customerId' => $customerId
        ];

        return view('reports/customer_statement', $data);
    }

    public function supplierStatement($supplierId = null)
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
            'supplierId' => $supplierId
        ];

        return view('reports/supplier_statement', $data);
    }

    public function export()
    {
        if (!hasPermission('reports', 'export')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        // Export logic here
        return redirect()->back()->with('success', 'Report exported successfully');
    }
}
