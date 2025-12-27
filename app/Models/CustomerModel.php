<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'company_id',
        'code',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'customer_type',
        'credit_limit',
        'payment_term_days',
        'status',
        'created_by',
        'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'company_id' => 'required|integer',
        'code' => 'required|max_length[50]',
        'name' => 'required|min_length[3]|max_length[255]',
        'email' => 'permit_empty|valid_email',
        'phone' => 'permit_empty|max_length[50]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Customer name is required',
        ],
        'code' => [
            'required' => 'Customer code is required',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['setCompanyId'];
    protected $beforeUpdate = ['setCompanyId'];

    protected function setCompanyId(array $data)
    {
        if (!isset($data['data']['company_id'])) {
            $data['data']['company_id'] = getCurrentCompanyId();
        }
        return $data;
    }

    /**
     * Get customers by company
     */
    public function getByCompany($companyId = null)
    {
        $companyId = $companyId ?? getCurrentCompanyId();
        return $this->where('company_id', $companyId)->findAll();
    }
}
