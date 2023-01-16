<?php

namespace laureandosi;

abstract class ReportPDF
{
    protected \FPDF $pdf;

    public function __construct(\FPDF $pdf = null)
    {
        require_once(join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'lib', 'fpdf184', 'fpdf.php')));
        $this->pdf = is_null($pdf) ? new \FPDF('P', 'mm', 'A4') : $pdf;
    }

    public function salva(string $filename): void
    {
        $this->pdf->Output('F', $filename);
    }

    abstract public function genera(): void;
}
