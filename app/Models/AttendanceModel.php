<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendances';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id', 'employee_id', 'attendance_date', 'clock_in', 
        'clock_out', 'break_duration', 'total_hours', 'overtime_hours', 
        'status', 'notes', 'created_by', 'updated_by'
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
        'employee_id' => 'required|integer',
        'attendance_date' => 'required|valid_date'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
