<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExportLibrary
{
    protected $spreadsheet;
    protected $sheet;
    public $currentRow = 1;
    protected $companyData;
    
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        
        $companyModel = new \App\Models\CompanyModel();
        $companyId = session()->get('current_company_id');
        $this->companyData = $companyModel->find($companyId);
        
        $this->spreadsheet->getProperties()
            ->setCreator($this->companyData['name'] ?? 'ERP System')
            ->setTitle('ERP Report');
    }
    
    public function addCompanyHeader($endColumn = 'F')
    {
        $this->sheet->setCellValue('A1', $this->companyData['name'] ?? 'Company Name');
        $this->sheet->mergeCells('A1:' . $endColumn . '1');
        $this->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $this->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $this->sheet->setCellValue('A2', $this->companyData['address'] ?? '');
        $this->sheet->mergeCells('A2:' . $endColumn . '2');
        $this->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $contact = 'Phone: ' . ($this->companyData['phone'] ?? '-') . ' | Email: ' . ($this->companyData['email'] ?? '-');
        $this->sheet->setCellValue('A3', $contact);
        $this->sheet->mergeCells('A3:' . $endColumn . '3');
        $this->sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $this->currentRow = 4;
    }
    
    public function addReportTitle($title, $endColumn = 'F')
    {
        $this->currentRow++;
        $this->sheet->setCellValue('A' . $this->currentRow, $title);
        $this->sheet->mergeCells('A' . $this->currentRow . ':' . $endColumn . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true)->setSize(14);
        $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->currentRow++;
    }
    
    public function addReportPeriod($period, $endColumn = 'F')
    {
        $this->sheet->setCellValue('A' . $this->currentRow, 'Period: ' . $period);
        $this->sheet->mergeCells('A' . $this->currentRow . ':' . $endColumn . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->currentRow += 2;
    }
    
    public function addTableHeader($headers)
    {
        $col = 'A';
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
            $this->sheet->getStyle($col . $this->currentRow)->getFont()->setBold(true);
            $this->sheet->getStyle($col . $this->currentRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $this->sheet->getStyle($col . $this->currentRow)->getFont()->getColor()->setRGB('FFFFFF');
            $this->sheet->getStyle($col . $this->currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $this->currentRow++;
    }
    
    public function addTableRow($data, $isTotal = false)
    {
        $col = 'A';
        foreach ($data as $value) {
            $this->sheet->setCellValue($col . $this->currentRow, $value);
            if ($isTotal) {
                $this->sheet->getStyle($col . $this->currentRow)->getFont()->setBold(true);
            }
            $col++;
        }
        $this->currentRow++;
    }
    
    public function applyTableBorders($startRow, $endRow, $endCol)
    {
        $range = 'A' . $startRow . ':' . $endCol . $endRow;
        $this->sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }
    
    public function autoSizeColumns($endCol = 'Z')
    {
        foreach (range('A', $endCol) as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    public function download($filename)
    {
        $writer = new Xlsx($this->spreadsheet);
        
        // Sanitize filename to prevent header injection
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $safeFilename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
