<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Libraries\AuthLibrary;
use CodeIgniter\HTTP\ResponseInterface;

class LoginController extends BaseController
{
    protected $authLib;
    
    public function __construct()
    {
        $this->authLib = new AuthLibrary();
    }
    
    public function index()
    {
        // If already logged in, redirect to dashboard
        if ($this->authLib->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/login');
    }
    
    public function authenticate()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'username' => 'required',
            'password' => 'required|min_length[6]',
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember') ? true : false;
        
        $result = $this->authLib->login($username, $password, $remember);
        
        if ($result['success']) {
            if ($result['force_password_change']) {
                return redirect()->to('/change-password')->with('warning', 'You must change your password.');
            }
            return redirect()->to('/dashboard')->with('success', 'Welcome back!');
        } else {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
    }
    
    public function logout()
    {
        $this->authLib->logout();
        return redirect()->to('/login')->with('success', 'You have been logged out successfully.');
    }
}

