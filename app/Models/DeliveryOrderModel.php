<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryOrderModel extends Model
{
    protected $table            = 'delivery_orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'sales_order_id', 'warehouse_id', 'delivery_number', 
        'delivery_date', 'reference', 'status', 'notes', 'delivered_by', 
        'received_by', 'created_by', 'updated_by'
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
        'delivery_number' => 'required|max_length[50]',
        'delivery_date' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
