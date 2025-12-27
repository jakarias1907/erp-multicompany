<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderModel extends Model
{
    protected $table            = 'sales_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'customer_id', 'quotation_id', 'order_number', 'order_date', 
        'delivery_date', 'reference', 'subtotal', 'tax_amount', 'discount', 
        'total', 'status', 'notes', 'created_by', 'updated_by'
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
        'customer_id' => 'required|integer',
        'order_number' => 'required|max_length[50]',
        'order_date' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
