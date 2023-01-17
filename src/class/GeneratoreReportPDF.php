<?php

namespace laureandosi;

class GeneratoreReportPDF
{
    private static GeneratoreReportPDF $instance;

    private function __construct()
    {
    }

    public static function getInstance(): GeneratoreReportPDF
    {
        if (!isset(self::$instance)) {
            require_once("ReportPDF.php");
            require_once("ReportPDFLaureando.php");
            require_once("ReportPDFCommissione.php");

            self::$instance = new GeneratoreReportPDF();
        }
        return self::$instance;
    }


    public static function generaReportPDFLaureando(Laureando $laureando): ReportPDFLaureando
    {
        $report = new ReportPDFLaureando($laureando);
        return $report->genera();
    }

    public static function generaReportPDFCommissione(array $laureandi): ReportPDFCommissione
    {
        $report = new ReportPDFCommissione($laureandi);
        return $report->genera();
    }
}
