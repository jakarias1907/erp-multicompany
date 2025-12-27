<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table            = 'stock_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'warehouse_id', 'product_id', 'transaction_type', 
        'reference_type', 'reference_id', 'quantity', 'unit_cost', 
        'transaction_date', 'notes', 'created_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'company_id' => 'required|integer',
        'warehouse_id' => 'required|integer',
        'product_id' => 'required|integer',
        'transaction_type' => 'required|in_list[in,out,adjustment,transfer]',
        'quantity' => 'required|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
