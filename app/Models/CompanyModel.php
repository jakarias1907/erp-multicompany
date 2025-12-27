<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'tax_id',
        'logo',
        'settings',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'code' => 'required|alpha_numeric|max_length[50]|is_unique[companies.code,id,{id}]',
        'email' => 'permit_empty|valid_email',
        'tax_id' => 'permit_empty|max_length[100]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Company name is required',
            'min_length' => 'Company name must be at least 3 characters',
        ],
        'code' => [
            'required' => 'Company code is required',
            'alpha_numeric' => 'Company code must be alphanumeric',
            'is_unique' => 'Company code already exists',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active companies
     */
    public function getActiveCompanies()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get company with details
     */
    public function getCompanyDetails($id)
    {
        return $this->find($id);
    }
}
