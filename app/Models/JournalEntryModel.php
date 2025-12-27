<?php

namespace App\Models;

use CodeIgniter\Model;

class JournalEntryModel extends Model
{
    protected $table            = 'journal_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'journal_number', 'transaction_date', 'reference', 
        'description', 'status', 'posted_at', 'approved_by', 'created_by', 'updated_by'
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
        'journal_number' => 'required|max_length[50]',
        'transaction_date' => 'required|valid_date',
        'status' => 'required|in_list[draft,posted,approved]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
