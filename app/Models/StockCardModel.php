<?php

namespace App\Models;

use CodeIgniter\Model;

class StockCardModel extends Model
{
    protected $table = 'stock_cards';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'company_id', 'warehouse_id', 'product_id', 'quantity', 'reserved_quantity'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getStockByWarehouse($warehouseId, $productId = null)
    {
        $builder = $this->builder();
        $builder->select('stock_cards.*, products.name as product_name, products.sku')
            ->join('products', 'products.id = stock_cards.product_id')
            ->where('stock_cards.warehouse_id', $warehouseId);
        
        if ($productId) {
            $builder->where('stock_cards.product_id', $productId);
        }
        
        return $builder->get()->getResultArray();
    }

    public function getAvailableQuantity($warehouseId, $productId)
    {
        $stock = $this->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
        
        if (!$stock) {
            return 0;
        }
        
        return $stock['quantity'] - $stock['reserved_quantity'];
    }
}
