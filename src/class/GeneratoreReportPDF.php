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

    public function generaReportPDFLaureando(Laureando $laureando, \FPDF $pdf = null): \FPDF
    {
        $pdf = $pdf ?? new \FPDF('P', 'mm', 'A4');

        $is_inf = is_a($laureando, LaureandoInformatica::class);

        $pdf->SetFont('Arial', '', 12);
        $pdf->AddPage();

        $pdf->Cell(0, 5, $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["CdL"], 0, 1, 'C');
        $pdf->Cell(0, 5, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'C');

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

        $pdf->Ln(3.5);
        $pdf->SetFontSize(10);

        $pdf->Rect($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 20, 20 + 10 * $is_inf);

        $pdf->Cell(80, 5, 'Media Pesata (M):', 0, 0);
        $pdf->Cell(0, 5, round($laureando->getMediaPesata(), 3), 0, 1);
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

    public function generaReportPDFLaureandoConSimulazione(Laureando $laureando, \FPDF $pdf = null): \FPDF
    {
        $pdf = $this->generaReportPDFLaureando($laureando, $pdf);

        $pdf->Ln(3);

        $pdf->Cell(0, 5, 'SIMULAZIONE DI VOTO DI LAUREA', 1, 1, 'C');

        list($t_min,$t_max,$t_step) = array_values($this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["par-T"]);
        list($c_min,$c_max,$c_step) = array_values($this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["par-C"]);

        $formula = $this->parametri_configurazione->getCorsiDiLaurea()[$laureando->CdL]["voto-laurea"];
        $formula = str_replace(array('M', 'CFU'), array($laureando->getMediaPesata(),$laureando->getCFU()), $formula);

        $parametro = '';
        $colonne = null;
        $righe = null;
        $width_col = null;

        $i_min = null;
        $i_max = null;
        $i_step = null;

        $informazioni_calcolo = null;

        if ($t_min != 0) {
            $formula = str_replace('C', 0, $formula);
            $parametro = 'T';

            $colonne = ($t_max - $t_min) / $t_step > 7 ? 2 : 1;
            $righe = ceil(($t_max - $t_min + 1) / $t_step / $colonne);
            $width_col = ($pdf->GetPageWidth() - 20) / $colonne;

            for ($i = 0; $i < $colonne; $i++) {
                $pdf->Cell($width_col / 2, 5, 'VOTO TESI (T)', 1, 0, 'C');
                $pdf->Cell($width_col / 2, 5, 'VOTO DI LAUREA', 1, 0, 'C');
            }
            $pdf->Ln();

            $i_min = $t_min;
            $i_max = $t_max;
            $i_step = $t_step;

            $informazioni_calcolo = "scegli voto di tesi, prendi il corrispondente voto di laurea ed arrotonda";
        } elseif ($c_min != 0) {
            $formula = str_replace('T', 0, $formula);
            $parametro = 'C';

            $colonne = ($c_max - $c_min) / $c_step > 7 ? 2 : 1;
            $righe = ceil(($c_max - $c_min + 1) / $c_step / $colonne);
            $width_col = ($pdf->GetPageWidth() - 20) / $colonne;

            for ($i = 0; $i < $colonne; $i++) {
                $pdf->Cell($width_col / 2, 5, 'VOTO COMMISSIONE (C)', 1, 0, 'C');
                $pdf->Cell($width_col / 2, 5, 'VOTO DI LAUREA', 1, 0, 'C');
            }
            $pdf->Ln();

            $i_min = $c_min;
            $i_max = $c_max;
            $i_step = $c_step;

            $informazioni_calcolo = is_a($laureando, LaureandoInformatica::class) ?
                "scegli voto commissione, prendi il corrispondente voto di laurea e somma il voto di tesi tra 1 e 3, quindi arrotonda" :
                "scegli voto commissione, prendi il corrispondente voto di laurea ed arrotonda";
        }

        $y_cord = $pdf->GetY();
        for ($i = $i_min, $col = 0; $col < $colonne && $i <= $i_max; $col++) {
            $pdf->SetY($y_cord);
            for ($j = 0; $j < $righe && $i <= $i_max; $j++, $i += $i_step) {
                $pdf->SetX(10 + $col * $width_col);
                $val = round(eval('return ' . str_replace($parametro, $i, $formula) . ';'), 3);

                $pdf->Cell($width_col / 2, 5, $i, 1, 0, 'C');
                $pdf->Cell($width_col / 2, 5, $val, 1, 1, 'C');
            }
        }
        $pdf->SetY($y_cord + $righe * 5);
        $pdf->Ln(4);

        $pdf->MultiCell(0, 5, 'VOTO DI LAUREA FINALE: ' . $informazioni_calcolo);

        return $pdf;
    }

    public function generaReportPDFCommissione(array $laureandi): \FPDF
    {
        $pdf = new \FPDF();
        foreach ($laureandi as $laureando) {
            $pdf = $this->generaReportPDFLaureandoConSimulazione($laureando, $pdf);
        }
        return $pdf;
    }
}
