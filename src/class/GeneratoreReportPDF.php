<?php

namespace laureandosi;

class GeneratoreReportPDF
{
    private ParametriConfigurazione $parametri_configurazione;

    public function __construct()
    {
        require_once(join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'lib', 'fpdf', 'fpdf.php')));
        require_once("ParametriConfigurazione.php");

        $this->parametri_configurazione = new ParametriConfigurazione();
    }

    public function generaReportPDFLaureando($laureando): \FPDF
    {
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetFont('Arial', '', 16);
        $pdf->AddPage();

        $pdf->Cell(0, 8, $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["CdL"], 0, 1, 'C');
        $pdf->Cell(0, 8, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'C');

        $pdf->Ln(3);
        $pdf->SetFontSize(10);

        $pdf->Cell(60, 5, 'Matricola:', 'TL', 0);
        $pdf->Cell(0, 5, $laureando->matricola, 'TR', 1);
        $pdf->Cell(60, 5, 'Nome:', 'L', 0);
        $pdf->Cell(0, 5, $laureando->nome, 'R', 1);
        $pdf->Cell(60, 5, 'Cognome:', 'L', 0);
        $pdf->Cell(0, 5, $laureando->cognome, 'R', 1);
        $pdf->Cell(60, 5, 'Email:', 'L', 0);
        $pdf->Cell(0, 5, $laureando->email, 'R', 1);
        $pdf->Cell(60, 5, 'Data:', 'BL', 0);
        $pdf->Cell(0, 5, $laureando->data_laurea, 'BR', 1);

        $pdf->Ln(1.5);

        $pdf->Cell($pdf->GetPageWidth() - 10 * 2 - 10 * 3, 5, 'ESAME', 1, 0, 'C');
        $pdf->Cell(10, 5, 'CFU', 1, 0, 'C');
        $pdf->Cell(10, 5, 'VOT', 1, 0, 'C');
        $pdf->Cell(10, 5, 'MED', 1, 1, 'C');

        $pdf->SetFontSize(8);

        foreach ($laureando->esami as $esame) {
            $pdf->Cell($pdf->GetPageWidth() - 10 * 2 - 10 * 3, 4, $esame->nome, 1, 0);
            $pdf->Cell(10, 4, $esame->cfu, 1, 0, 'C');
            $pdf->Cell(10, 4, $esame->voto, 1, 0, 'C');
            $pdf->Cell(10, 4, $esame->is_avg ? 'X' : '', 1, 1, 'C');
        }

        $pdf->Ln(6);
        $pdf->SetFontSize(10);

        $pdf->Cell(80, 5, 'Media Pesata (M):', 'TL', 0);
        $pdf->Cell(0, 5, $laureando->getMediaPesata(), 'TR', 1);
        $pdf->Cell(80, 5, 'Crediti che fanno media (CFU):', 'L', 0);
        $pdf->Cell(0, 5, $laureando->getCFUInAVG(), 'R', 1);
        $pdf->Cell(80, 5, 'Crediti curriculari conseguiti:', 'L', 0);
        $pdf->Cell(0, 5, $laureando->getCFU() . '/' .
            $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["tot-CFU"], 'R', 1);
        $pdf->Cell(80, 5, 'Formula calcolo voto di laurea:', 'BL', 0);
        $pdf->Cell(0, 5, $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["voto-laurea"], 'BR', 1);

        return $pdf;
    }
}
