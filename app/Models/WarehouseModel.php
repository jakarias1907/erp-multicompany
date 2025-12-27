<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'company_id', 'code', 'name', 'address', 'manager_id', 'status', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $beforeInsert = ['setCompanyId'];
    protected $beforeUpdate = ['setCompanyId'];

    protected function setCompanyId(array $data)
    {
        if (!isset($data['data']['company_id'])) {
            $data['data']['company_id'] = getCurrentCompanyId();
        }
        return $data;
    }

    public function getByCompany($companyId = null)
    {
        $companyId = $companyId ?? getCurrentCompanyId();
        return $this->where('company_id', $companyId)->findAll();
    }
}
