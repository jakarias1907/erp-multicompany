<?php

namespace App\Models;

use CodeIgniter\Model;

class JournalEntryLineModel extends Model
{
    protected $table            = 'journal_entry_lines';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'journal_entry_id', 'account_id', 'description', 'debit', 'credit'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'journal_entry_id' => 'required|integer',
        'account_id' => 'required|integer',
        'debit' => 'required|decimal',
        'credit' => 'required|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
