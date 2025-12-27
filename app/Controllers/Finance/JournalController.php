<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\JournalEntryModel;
use App\Models\JournalEntryLineModel;
use App\Models\ChartOfAccountModel;

class JournalController extends BaseController
{
    protected $journalModel;
    protected $journalLineModel;
    protected $accountModel;

    public function __construct()
    {
        $this->journalModel = new JournalEntryModel();
        $this->journalLineModel = new JournalEntryLineModel();
        $this->accountModel = new ChartOfAccountModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('journals', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Journal Entries',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Journal Entries']
            ]
        ];

        return view('finance/journal/index', $data);
    }

    public function datatable()
    {
        if (!hasPermission('journals', 'read')) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        $builder = $this->journalModel->builder();
        $builder->where('company_id', $companyId)
                ->where('deleted_at', null);

        if ($searchValue) {
            $builder->groupStart()
                ->like('journal_number', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        $totalFiltered = $builder->countAllResults(false);
        $journals = $builder->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($journals as $journal) {
            $statusBadges = [
                'draft' => 'secondary',
                'posted' => 'success',
                'approved' => 'primary'
            ];
            
            $statusBadge = '<span class="badge badge-' . ($statusBadges[$journal['status']] ?? 'secondary') . '">' 
                . ucfirst($journal['status']) . '</span>';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('finance/journal/view/' . $journal['id']) . '" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . base_url('finance/journal/print/' . $journal['id']) . '" class="btn btn-sm btn-secondary" title="Print" target="_blank">
                        <i class="fas fa-print"></i>
                    </a>';
            
            if ($journal['status'] == 'draft') {
                $actions .= '
                    <a href="' . base_url('finance/journal/edit/' . $journal['id']) . '" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-success btn-post" data-id="' . $journal['id'] . '" title="Post">
                        <i class="fas fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $journal['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
            } elseif ($journal['status'] == 'posted' && hasPermission('journals', 'approve')) {
                $actions .= '
                    <button type="button" class="btn btn-sm btn-primary btn-approve" data-id="' . $journal['id'] . '" title="Approve">
                        <i class="fas fa-check-double"></i>
                    </button>';
            }
            
            $actions .= '</div>';

            $data[] = [
                'journal_number' => esc($journal['journal_number']),
                'transaction_date' => date('d M Y', strtotime($journal['transaction_date'])),
                'description' => esc($journal['description']),
                'status' => $statusBadge,
                'actions' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $this->journalModel->where('company_id', $companyId)->countAllResults(),
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        if (!hasPermission('journals', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $accounts = $this->accountModel
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->findAll();

        // Generate next journal number
        $lastJournal = $this->journalModel
            ->where('company_id', $companyId)
            ->orderBy('id', 'DESC')
            ->first();
        
        $nextNumber = 'JE-' . date('Ym') . '-' . str_pad(($lastJournal ? intval(substr($lastJournal['journal_number'], -4)) + 1 : 1), 4, '0', STR_PAD_LEFT);

        $data = [
            'title' => 'Create Journal Entry',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Journal Entries', 'url' => base_url('finance/journal')],
                ['label' => 'Create']
            ],
            'accounts' => $accounts,
            'journalNumber' => $nextNumber
        ];

        return view('finance/journal/create', $data);
    }

    public function store()
    {
        if (!hasPermission('journals', 'create')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'journal_number' => 'required|max_length[50]',
            'transaction_date' => 'required|valid_date',
            'description' => 'required|max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Validate journal lines
        $accounts = $this->request->getPost('account_id');
        $debits = $this->request->getPost('debit');
        $credits = $this->request->getPost('credit');
        $descriptions = $this->request->getPost('line_description');

        if (!$accounts || count($accounts) < 2) {
            return redirect()->back()->withInput()->with('error', 'Journal entry must have at least 2 lines');
        }

        // Validate balance
        $totalDebit = array_sum($debits);
        $totalCredit = array_sum($credits);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return redirect()->back()->withInput()->with('error', 'Total debit must equal total credit');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $journalData = [
                'company_id' => getCurrentCompanyId(),
                'journal_number' => $this->request->getPost('journal_number'),
                'transaction_date' => $this->request->getPost('transaction_date'),
                'reference' => $this->request->getPost('reference'),
                'description' => $this->request->getPost('description'),
                'status' => 'draft',
                'created_by' => getCurrentUserId()
            ];

            $journalId = $this->journalModel->insert($journalData);

            // Insert journal lines
            foreach ($accounts as $index => $accountId) {
                if ($accountId && ($debits[$index] > 0 || $credits[$index] > 0)) {
                    $this->journalLineModel->insert([
                        'journal_entry_id' => $journalId,
                        'account_id' => $accountId,
                        'description' => $descriptions[$index] ?? '',
                        'debit' => $debits[$index] ?? 0,
                        'credit' => $credits[$index] ?? 0
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to create journal entry');
            }

            logActivity('create', 'journals', "Created journal entry: {$journalData['journal_number']}", $journalId);
            return redirect()->to('/finance/journal')->with('success', 'Journal entry created successfully');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        if (!hasPermission('journals', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $journal = $this->journalModel->find($id);
        if (!$journal) {
            return redirect()->to('/finance/journal')->with('error', 'Journal entry not found');
        }

        $db = \Config\Database::connect();
        $lines = $db->table('journal_entry_lines jel')
            ->select('jel.*, coa.code as account_code, coa.name as account_name')
            ->join('chart_of_accounts coa', 'coa.id = jel.account_id')
            ->where('jel.journal_entry_id', $id)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'View Journal Entry',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Journal Entries', 'url' => base_url('finance/journal')],
                ['label' => 'View']
            ],
            'journal' => $journal,
            'lines' => $lines
        ];

        return view('finance/journal/view', $data);
    }

    public function post($id)
    {
        if (!hasPermission('journals', 'update')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $journal = $this->journalModel->find($id);
        if (!$journal || $journal['status'] != 'draft') {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot post this journal entry']);
        }

        if ($this->journalModel->update($id, ['status' => 'posted', 'posted_at' => date('Y-m-d H:i:s')])) {
            logActivity('update', 'journals', "Posted journal entry: {$journal['journal_number']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Journal entry posted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to post journal entry']);
    }

    public function approve($id)
    {
        if (!hasPermission('journals', 'approve')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $journal = $this->journalModel->find($id);
        if (!$journal || $journal['status'] != 'posted') {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot approve this journal entry']);
        }

        if ($this->journalModel->update($id, ['status' => 'approved', 'approved_by' => getCurrentUserId()])) {
            logActivity('update', 'journals', "Approved journal entry: {$journal['journal_number']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Journal entry approved successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to approve journal entry']);
    }

    public function delete($id)
    {
        if (!hasPermission('journals', 'delete')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $journal = $this->journalModel->find($id);
        if (!$journal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Journal entry not found']);
        }

        if ($journal['status'] != 'draft') {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete posted/approved journal entry']);
        }

        if ($this->journalModel->delete($id)) {
            logActivity('delete', 'journals', "Deleted journal entry: {$journal['journal_number']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Journal entry deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete journal entry']);
    }
}
