<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'user_name' => session()->get('full_name'),
            'company_name' => session()->get('current_company_name'),
        ];
        
        return view('dashboard/index', $data);
    }
}

