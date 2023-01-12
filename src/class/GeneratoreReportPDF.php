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

    public function generaReportPDFLaureando($laureando, $pdf = null): \FPDF
    {
        if ($pdf == null) {
            $pdf = new \FPDF('P', 'mm', 'A4');
        }

        $is_inf = is_a($laureando, LaureandoInformatica::class);

        $pdf->SetFont('Arial', '', 16);
        $pdf->AddPage();

        $pdf->Cell(0, 8, $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["CdL"], 0, 1, 'C');
        $pdf->Cell(0, 8, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'C');

        $pdf->Ln(3);
        $pdf->SetFontSize(10);

        $pdf->Rect($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 20, 5 * (5 + $is_inf));

        $pdf->Cell(60, 5, 'Matricola:', 0, 0);
        $pdf->Cell(0, 5, $laureando->matricola, 0, 1);
        $pdf->Cell(60, 5, 'Nome:', 0, 0);
        $pdf->Cell(0, 5, $laureando->nome, 0, 1);
        $pdf->Cell(60, 5, 'Cognome:', 0, 0);
        $pdf->Cell(0, 5, $laureando->cognome, 0, 1);
        $pdf->Cell(60, 5, 'Email:', 0, 0);
        $pdf->Cell(0, 5, $laureando->email, 0, 1);
        $pdf->Cell(60, 5, 'Data:', 0, 0);
        $pdf->Cell(0, 5, date_format($laureando->data_laurea, "Y-m-d"), 0, 1);
        if ($is_inf) {
            $pdf->Cell(60, 5, 'BONUS:', 0, 0);
            $pdf->Cell(0, 5, $laureando->getBonusINF() ? 'SI' : 'NO', 0, 1);
        }

        $pdf->Ln(1.5);

        $pdf->Cell($pdf->GetPageWidth() - 10 * (5 + $is_inf), 5, 'ESAME', 1, 0, 'C');
        $pdf->Cell(10, 5, 'CFU', 1, 0, 'C');
        $pdf->Cell(10, 5, 'VOT', 1, 0, 'C');
        $pdf->Cell(10, 5, 'MED', 1, 0, 'C');
        if ($is_inf) {
            $pdf->Cell(10, 5, 'INF', 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFontSize(8);

        foreach ($laureando->esami as $esame) {
            if ($esame->in_cdl) {
                $pdf->Cell($pdf->GetPageWidth()  - 10 * (5 + $is_inf), 4, $esame->nome, 1, 0);
                $pdf->Cell(10, 4, $esame->cfu, 1, 0, 'C');
                $pdf->Cell(10, 4, $esame->voto, 1, 0, 'C');
                $pdf->Cell(10, 4, $esame->in_avg ? 'X' : '', 1, 0, 'C');
                if ($is_inf) {
                    $pdf->Cell(10, 4, $esame->in_inf ? 'X' : '', 1, 0, 'C');
                }
                $pdf->Ln();
            }
        }

        $pdf->Ln(6);
        $pdf->SetFontSize(10);

        $pdf->Rect($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 20, 20 + 10 * $is_inf);

        $pdf->Cell(80, 5, 'Media Pesata (M):', 0, 0);
        $pdf->Cell(0, 5, $laureando->getMediaPesata(), 0, 1);
        $pdf->Cell(80, 5, 'Crediti che fanno media (CFU):', 0, 0);
        $pdf->Cell(0, 5, $laureando->getCFUInAVG(), 0, 1);
        $pdf->Cell(80, 5, 'Crediti curriculari conseguiti:', 0, 0);
        $pdf->Cell(0, 5, $laureando->getCFU() . '/' .
            $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["tot-CFU"], 0, 1);
        if ($is_inf) {
            $pdf->Cell(80, 5, 'Voto di tesi (T):', 0, 0);
            $pdf->Cell(0, 5, 0, 0, 1);
        }
        $pdf->Cell(80, 5, 'Formula calcolo voto di laurea:', 0, 0);
        $pdf->Cell(0, 5, $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["voto-laurea"], 0, 1);
        if ($is_inf) {
            $pdf->Cell(80, 5, 'Media pesata esami INF:', 0, 0);
            $pdf->Cell(0, 5, $laureando->getMediaPesataInINF(), 0, 1);
        }

        return $pdf;
    }

	public function generaReportPDFLaureandoConSimulazione($laureando, $pdf = null): \FPDF
	{
		$pdf = $this->generaReportPDFLaureando($laureando, $pdf);

		//TODO: da continuare

		return $pdf;
	}
}
