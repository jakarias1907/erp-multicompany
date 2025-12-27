<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'company_id',
        'sku',
        'name',
        'category_id',
        'unit_id',
        'price',
        'cost',
        'stock_alert_level',
        'description',
        'image',
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
        'sku' => 'required|max_length[100]',
        'name' => 'required|min_length[3]|max_length[255]',
        'price' => 'required|decimal',
        'cost' => 'permit_empty|decimal',
    ];

    protected $validationMessages = [
        'sku' => [
            'required' => 'SKU is required',
        ],
        'name' => [
            'required' => 'Product name is required',
        ],
        'price' => [
            'required' => 'Price is required',
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
     * Get products by company
     */
    public function getByCompany($companyId = null)
    {
        $companyId = $companyId ?? getCurrentCompanyId();
        return $this->where('company_id', $companyId)->findAll();
    }

    /**
     * Get product with category and unit
     */
    public function getProductDetails($id)
    {
        return $this->select('products.*, 
                product_categories.name as category_name,
                units.name as unit_name')
            ->join('product_categories', 'product_categories.id = products.category_id', 'left')
            ->join('units', 'units.id = products.unit_id', 'left')
            ->where('products.id', $id)
            ->first();
    }
}
