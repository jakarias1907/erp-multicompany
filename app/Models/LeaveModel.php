<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveModel extends Model
{
    protected $table            = 'leaves';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'employee_id', 'leave_type', 'start_date', 'end_date', 
        'days', 'reason', 'status', 'approved_by', 'approved_at', 
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
        'leave_type' => 'required|in_list[annual,sick,casual,unpaid]',
        'start_date' => 'required|valid_date',
        'end_date' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
