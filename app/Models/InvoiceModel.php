<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'company_id', 'customer_id', 'invoice_number', 'invoice_date', 'due_date',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'paid_amount',
        'status', 'notes', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'company_id' => 'required|integer',
        'customer_id' => 'required|integer',
        'invoice_number' => 'required',
        'invoice_date' => 'required|valid_date',
        'total_amount' => 'required|decimal'
    ];

    protected $beforeInsert = ['setCompanyId'];
    protected $beforeUpdate = ['setCompanyId'];

    protected function setCompanyId(array $data)
    {
        if (!isset($data['data']['company_id'])) {
            $data['data']['company_id'] = getCurrentCompanyId();
        }
        return $data;
    }

    public function getWithCustomer($invoiceId)
    {
        return $this->select('invoices.*, customers.name as customer_name, customers.email as customer_email')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->where('invoices.id', $invoiceId)
            ->first();
    }
}
