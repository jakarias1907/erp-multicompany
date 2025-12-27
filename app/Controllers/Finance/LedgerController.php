<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\ChartOfAccountModel;
use App\Models\JournalEntryLineModel;

class LedgerController extends BaseController
{
    protected $accountModel;
    protected $journalLineModel;

    public function __construct()
    {
        $this->accountModel = new ChartOfAccountModel();
        $this->journalLineModel = new JournalEntryLineModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'General Ledger & Reports',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Ledger & Reports']
            ]
        ];

        return view('finance/ledger/index', $data);
    }

    public function generalLedger()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        $accountId = $this->request->getGet('account_id');

        $accounts = $this->accountModel
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->orderBy('code', 'ASC')
            ->findAll();

        $ledgerData = [];

        if ($accountId) {
            $db = \Config\Database::connect();
            $transactions = $db->table('journal_entry_lines jel')
                ->select('je.transaction_date, je.journal_number, je.description, jel.debit, jel.credit, jel.description as line_description')
                ->join('journal_entries je', 'je.id = jel.journal_entry_id')
                ->where('jel.account_id', $accountId)
                ->where('je.status', 'posted')
                ->where('je.transaction_date >=', $startDate)
                ->where('je.transaction_date <=', $endDate)
                ->where('je.company_id', $companyId)
                ->orderBy('je.transaction_date', 'ASC')
                ->get()
                ->getResultArray();

            $balance = 0;
            foreach ($transactions as &$trans) {
                $balance += ($trans['debit'] - $trans['credit']);
                $trans['balance'] = $balance;
            }

            $ledgerData = $transactions;
        }

        $data = [
            'title' => 'General Ledger',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Ledger & Reports', 'url' => base_url('finance/ledger')],
                ['label' => 'General Ledger']
            ],
            'accounts' => $accounts,
            'ledgerData' => $ledgerData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'accountId' => $accountId
        ];

        return view('finance/ledger/general_ledger', $data);
    }

    public function trialBalance()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $asOfDate = $this->request->getGet('as_of_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        
        $balances = $db->query("
            SELECT 
                coa.id,
                coa.code,
                coa.name,
                coa.account_type,
                COALESCE(SUM(jel.debit), 0) as total_debit,
                COALESCE(SUM(jel.credit), 0) as total_credit
            FROM chart_of_accounts coa
            LEFT JOIN journal_entry_lines jel ON jel.account_id = coa.id
            LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
            WHERE coa.company_id = ?
            AND coa.deleted_at IS NULL
            AND (je.transaction_date <= ? OR je.id IS NULL)
            AND (je.status = 'posted' OR je.id IS NULL)
            GROUP BY coa.id, coa.code, coa.name, coa.account_type
            ORDER BY coa.code
        ", [$companyId, $asOfDate])->getResultArray();

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($balances as &$balance) {
            $balance['debit_balance'] = $balance['total_debit'] - $balance['total_credit'] > 0 
                ? $balance['total_debit'] - $balance['total_credit'] : 0;
            $balance['credit_balance'] = $balance['total_credit'] - $balance['total_debit'] > 0 
                ? $balance['total_credit'] - $balance['total_debit'] : 0;
            
            $totalDebit += $balance['debit_balance'];
            $totalCredit += $balance['credit_balance'];
        }

        $data = [
            'title' => 'Trial Balance',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Ledger & Reports', 'url' => base_url('finance/ledger')],
                ['label' => 'Trial Balance']
            ],
            'balances' => $balances,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'asOfDate' => $asOfDate
        ];

        return view('finance/ledger/trial_balance', $data);
    }

    public function balanceSheet()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $asOfDate = $this->request->getGet('as_of_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        
        $accounts = $db->query("
            SELECT 
                coa.id,
                coa.code,
                coa.name,
                coa.account_type,
                COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as balance
            FROM chart_of_accounts coa
            LEFT JOIN journal_entry_lines jel ON jel.account_id = coa.id
            LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
            WHERE coa.company_id = ?
            AND coa.deleted_at IS NULL
            AND coa.account_type IN ('asset', 'liability', 'equity')
            AND (je.transaction_date <= ? OR je.id IS NULL)
            AND (je.status = 'posted' OR je.id IS NULL)
            GROUP BY coa.id, coa.code, coa.name, coa.account_type
            HAVING balance != 0
            ORDER BY coa.account_type, coa.code
        ", [$companyId, $asOfDate])->getResultArray();

        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        foreach ($accounts as $account) {
            if ($account['account_type'] == 'asset') {
                $assets[] = $account;
                $totalAssets += $account['balance'];
            } elseif ($account['account_type'] == 'liability') {
                $liabilities[] = $account;
                $totalLiabilities += abs($account['balance']);
            } elseif ($account['account_type'] == 'equity') {
                $equity[] = $account;
                $totalEquity += abs($account['balance']);
            }
        }

        $data = [
            'title' => 'Balance Sheet',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Ledger & Reports', 'url' => base_url('finance/ledger')],
                ['label' => 'Balance Sheet']
            ],
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'asOfDate' => $asOfDate
        ];

        return view('finance/ledger/balance_sheet', $data);
    }

    public function incomeStatement()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        
        $accounts = $db->query("
            SELECT 
                coa.id,
                coa.code,
                coa.name,
                coa.account_type,
                COALESCE(SUM(jel.credit), 0) - COALESCE(SUM(jel.debit), 0) as amount
            FROM chart_of_accounts coa
            LEFT JOIN journal_entry_lines jel ON jel.account_id = coa.id
            LEFT JOIN journal_entries je ON je.id = jel.journal_entry_id
            WHERE coa.company_id = ?
            AND coa.deleted_at IS NULL
            AND coa.account_type IN ('revenue', 'expense')
            AND je.transaction_date BETWEEN ? AND ?
            AND je.status = 'posted'
            GROUP BY coa.id, coa.code, coa.name, coa.account_type
            HAVING amount != 0
            ORDER BY coa.account_type, coa.code
        ", [$companyId, $startDate, $endDate])->getResultArray();

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($accounts as $account) {
            if ($account['account_type'] == 'revenue') {
                $revenues[] = $account;
                $totalRevenue += $account['amount'];
            } elseif ($account['account_type'] == 'expense') {
                $expenses[] = $account;
                $totalExpense += abs($account['amount']);
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        $data = [
            'title' => 'Income Statement (P&L)',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Ledger & Reports', 'url' => base_url('finance/ledger')],
                ['label' => 'Income Statement']
            ],
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netIncome' => $netIncome,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        return view('finance/ledger/income_statement', $data);
    }

    public function cashFlow()
    {
        if (!hasPermission('reports', 'read')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $companyId = getCurrentCompanyId();
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        $data = [
            'title' => 'Cash Flow Statement',
            'breadcrumbs' => [
                ['label' => 'Finance', 'url' => '#'],
                ['label' => 'Ledger & Reports', 'url' => base_url('finance/ledger')],
                ['label' => 'Cash Flow']
            ],
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        return view('finance/ledger/cash_flow', $data);
    }
}
