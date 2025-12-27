<?php

namespace App\Models;

use CodeIgniter\Model;

class BillModel extends Model
{
    protected $table            = 'bills';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'supplier_id', 'bill_number', 'bill_date', 'due_date', 
        'reference', 'subtotal', 'tax_amount', 'discount', 'total', 
        'paid_amount', 'status', 'notes', 'created_by', 'updated_by'
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
        'supplier_id' => 'required|integer',
        'bill_number' => 'required|max_length[50]',
        'bill_date' => 'required|valid_date',
        'total' => 'required|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
