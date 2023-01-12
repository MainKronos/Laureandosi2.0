<?php

namespace laureandosi;

class API
{
    private ParametriConfigurazione $parametri_configurazione;
    private GeneratoreReportPDF $generatore_report;
    private string $report_path = ABSPATH . DIRECTORY_SEPARATOR . 'report';

    public function __construct()
    {
        require_once("ParametriConfigurazione.php");
        require_once("Laureando.php");
        require_once("LaureandoInformatica.php");
        require_once("GeneratoreReportPDF.php");

        $this->parametri_configurazione = new ParametriConfigurazione();
        $this->generatore_report = new GeneratoreReportPDF();
    }

    public function test()
    {
        return json_encode("test");
    }

    public function GETCorsiDiLaurea()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $val = array_values($this->parametri_configurazione->getCorsiDiLaurea());

            for ($i = 0; $i < count($val); $i++) {
                $val[$i] = array_filter(
                    $val[$i],
                    function ($key) {
                        return $key == "CdL" || $key == "CdL-short";
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }
            return json_encode($val);
        }
        return http_response_code(405);
    }

    public function POSTCreaProspetti()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = json_decode(file_get_contents('php://input'), true);

            $path = join(DIRECTORY_SEPARATOR, array(
                $this->report_path,
                $data["data_laurea"],
                $data["corso_laurea"],
            ));

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $laureandi = array();
            try {
                foreach ($data["matricole"] as $matricola) {
                    $matricola = (int) $matricola;
                    $corso_laurea = $data["corso_laurea"];
                    $data_laurea = date_create($data["data_laurea"]);

                    $laureando = $corso_laurea != "t-inf" ?
                        new Laureando($matricola, $corso_laurea, $data_laurea) :
                        new LaureandoInformatica($matricola, $corso_laurea, $data_laurea);

                    $this->generatore_report->generaReportPDFLaureando($laureando)->Output(
                        'F',
                        $path . DIRECTORY_SEPARATOR . $laureando->matricola . '.pdf'
                    );

                    $laureandi[] = $laureando;
                }
				$this->generatore_report->generaReportPDFCommissione($laureandi)->Output(
					'F',
					$path . DIRECTORY_SEPARATOR . 'all.pdf'
				);
                return json_encode(array("message" => count($laureandi) . " prospetti creati con successo."));
            } catch (\Exception $e) {
                http_response_code(400);
                return json_encode(array("message" => "ERRORE: ".$e->getMessage()));
            }
        }
        http_response_code(405);
        return null;
    }

	public function GETApriProspetto(){
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$corso_laurea = $_GET["corso_laurea"];
			$data_laurea = $_GET["data_laurea"];

			$file = join(DIRECTORY_SEPARATOR, array(
				$this->report_path,
				$data_laurea,
				$corso_laurea,
				'all.pdf'
			));

			if (file_exists($file)) {
				header('Content-type: application/pdf');
				header('Content-Disposition: inline; filename="all.pdf"');
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: ' . filesize($file));
				header('Accept-Ranges: bytes');
				readfile($file);
				return json_encode(array("message" => "Prospetto aperto con successo."));
			} else {
				http_response_code(404);
				return json_encode(array("message" => "ERRORE: Il prospetto non esiste."));
			}
		}
		http_response_code(405);
		return null;
	}
}
