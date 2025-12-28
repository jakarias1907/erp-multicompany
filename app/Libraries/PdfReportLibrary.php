<?php

namespace App\Libraries;

use TCPDF;

class PdfReportLibrary extends TCPDF
{
    protected $companyData;
    
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        
        // Load company data
        $companyModel = new \App\Models\CompanyModel();
        $companyId = session()->get('current_company_id');
        $this->companyData = $companyModel->find($companyId);
        
        $this->SetCreator('ERP Multi-Company System');
        $this->SetAuthor($this->companyData['name'] ?? 'ERP System');
        
        $this->setHeaderFont(['helvetica', '', 10]);
        $this->setFooterFont(['helvetica', '', 8]);
        
        $this->SetMargins(15, 40, 15);
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(10);
        $this->SetAutoPageBreak(TRUE, 25);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }
    
    public function Header()
    {
        // Company logo
        if (!empty($this->companyData['logo'])) {
            // Sanitize filename to prevent path traversal
            $logoFilename = basename($this->companyData['logo']);
            $logoPath = FCPATH . 'uploads/companies/' . $logoFilename;
            if (file_exists($logoPath) && is_file($logoPath)) {
                $this->Image($logoPath, 15, 10, 30);
            }
        }
        
        // Company info
        $this->SetFont('helvetica', 'B', 16);
        $this->SetXY(50, 10);
        $this->Cell(0, 5, $this->companyData['name'] ?? 'Company Name', 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 9);
        $this->SetX(50);
        $this->Cell(0, 4, $this->companyData['address'] ?? '', 0, 1, 'L');
        $this->SetX(50);
        $this->Cell(0, 4, 'Phone: ' . ($this->companyData['phone'] ?? '-') . ' | Email: ' . ($this->companyData['email'] ?? '-'), 0, 1, 'L');
        
        $this->Ln(5);
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());
    }
    
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
        $this->SetY(-15);
        $this->Cell(0, 10, 'Printed: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
    }
    
    public function setReportTitle($title)
    {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, $title, 0, 1, 'C');
        $this->Ln(3);
    }
    
    public function setReportPeriod($period)
    {
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 6, 'Period: ' . $period, 0, 1, 'C');
        $this->Ln(5);
    }
}
