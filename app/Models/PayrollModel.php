<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table            = 'payrolls';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'employee_id', 'period_start', 'period_end', 
        'basic_salary', 'allowances', 'deductions', 'overtime_pay', 
        'gross_salary', 'tax', 'net_salary', 'status', 'paid_date', 
        'notes', 'created_by', 'updated_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'company_id' => 'required|integer',
        'employee_id' => 'required|integer',
        'period_start' => 'required|valid_date',
        'period_end' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
