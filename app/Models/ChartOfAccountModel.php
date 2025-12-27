<?php

namespace App\Models;

use CodeIgniter\Model;

class ChartOfAccountModel extends Model
{
    protected $table            = 'chart_of_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'parent_id', 'code', 'name', 'account_type', 
        'description', 'is_active', 'created_by', 'updated_by'
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
        'code' => 'required|max_length[20]',
        'name' => 'required|max_length[255]',
        'account_type' => 'required|in_list[asset,liability,equity,revenue,expense]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
