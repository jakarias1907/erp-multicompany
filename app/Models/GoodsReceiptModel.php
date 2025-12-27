<?php

namespace App\Models;

use CodeIgniter\Model;

class GoodsReceiptModel extends Model
{
    protected $table            = 'goods_receipts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'po_id', 'warehouse_id', 'gr_number', 'receipt_date', 
        'reference', 'status', 'notes', 'received_by', 'created_by', 'updated_by'
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
        'gr_number' => 'required|max_length[50]',
        'receipt_date' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
