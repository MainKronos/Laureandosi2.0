<?php

namespace test;

class UnitTest
{
    private static $casi_test;
    private static \laureandosi\API $API;
	private static \laureandosi\ParametriConfigurazione $parametri_configurazione;

    public static function run()
    {
        self::setUp();
        self::testGETCorsiDiLaurea();
        self::testPOSTCreaReport();
        self::testGETApriReport();
        // self::testGETCarriera();
    }

    private static function setUp()
    {
        require_once(join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'class', 'API.php')));
        require_once(join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'class', 'ParametriConfigurazione.php')));
        require_once(join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'class', 'ReportPDF.php')));
        require_once(join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'class', 'ReportPDFLaureando.php')));
		require_once(join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'class', 'ReportPDFLaureandoConSimulazione.php')));
        self::$API = \laureandosi\API::getInstance();
		self::$parametri_configurazione = \laureandosi\ParametriConfigurazione::getInstance();
        self::$casi_test = array(
            array(
                "matricole" => array(123456),
                "corso_laurea" => "t-inf",
                "data_laurea" => "2023-01-04"
            ),
            array(
                "matricole" => array(234567),
                "corso_laurea" => "m-ele",
                "data_laurea" => "2023-01-04"
            ),
            array(
                "matricole" => array(345678),
                "corso_laurea" => "t-inf",
                "data_laurea" => "2023-01-04"
            ),
            array(
                "matricole" => array(456789),
                "corso_laurea" => "m-tel",
                "data_laurea" => "2023-01-04"
            ),
            array(
                "matricole" => array(567890),
                "corso_laurea" => "m-cyb",
                "data_laurea" => "2023-01-04"
            )
        );
    }

    private static function testGETCorsiDiLaurea()
    {
        echo "<h3>testGETCorsiDiLaurea: </h3>";
        $corsi_laurea = self::$API->getCorsiDiLaurea();
		$string = print_r($corsi_laurea, true);
		echo "status: <ul>";

		echo "<li>valore di ritorno: ";
		$val = array_values(self::$parametri_configurazione::getCorsiDiLaurea());
		for ($i = 0; $i < count($val); $i++) {
			$val[$i] = array_filter(
				$val[$i],
				function ($key) {
					return $key == "cdl" || $key == "cdl-short";
				},
				ARRAY_FILTER_USE_KEY
			);
		}
		$val = json_encode($val);
        if(assert(strcmp($corsi_laurea, $val) == 0, "valore di ritorno")){
			echo "<span style='color: green;'>OK</span>";
		}
		echo "</li>";

		echo "</ul>";
    }

	private static function testPOSTCreaReport(){
		echo "<h3>testPOSTCreaReport: </h3>";

		echo "status: <ul>";
		foreach (self::$casi_test as $caso_test) {
			$_SERVER["REQUEST_METHOD"] = "POST";
			$ret = self::$API::POSTCreaReport(json_encode($caso_test));

			echo "<li>Matricola: " . $caso_test["matricole"][0] . "<ul>";

			echo "<li>valore di ritorno: ";
			if(assert(isset(json_decode($ret, true)["message"]), "valore di ritorno")){
				echo "<span style='color: green;'>OK</span>";
			}
			echo "</li>";

			echo "<li>creazione cartella: ";
			$report_path = join(DIRECTORY_SEPARATOR, array(
				ABSPATH,
				"report",
				$caso_test["data_laurea"],
				$caso_test["corso_laurea"],
			));
			if(assert(file_exists($report_path), "creazione cartella")){
				echo "<span style='color: green;'>OK</span>";
			}
			echo "</li>";

			echo "<li>creazione file: ";
			$report_file = join(DIRECTORY_SEPARATOR, array(
				$report_path,
				$caso_test["matricole"][0] . ".pdf"
			));
			if(assert(file_exists($report_file), "creazione file")){
				echo "<span style='color: green;'>OK</span>";
			}
			echo "</li>";

			echo "</ul></li>";
		}
		echo "</ul>";
	}

	private static function testGETApriReport(){
		echo "<h3>testGETApriReport: </h3>";

		echo "status: <ul>";

		for($i = 0; $i < count(self::$casi_test); $i++){
			$caso_test = self::$casi_test[$i];


			$report_file_url = join(DIRECTORY_SEPARATOR, array(
				"..",
				"report",
				$caso_test["data_laurea"],
				$caso_test["corso_laurea"],
				$caso_test["matricole"][0] . "_sim.pdf"
			));

			$report_file = join(DIRECTORY_SEPARATOR, array(
				ABSPATH,
				"report",
				$caso_test["data_laurea"],
				$caso_test["corso_laurea"],
				$caso_test["matricole"][0] . "_sim.pdf"
			));

			$laureando = $caso_test["corso_laurea"] != "t-inf" ?
				new \laureandosi\Laureando($caso_test["matricole"][0], $caso_test["corso_laurea"], date_create($caso_test["data_laurea"])) :
				new \laureandosi\LaureandoInformatica($caso_test["matricole"][0], $caso_test["corso_laurea"], date_create($caso_test["data_laurea"]));

			(new \laureandosi\ReportPDFLaureandoConSimulazione($laureando))->genera()->salva($report_file);

			echo "<li>Matricola: " . $caso_test["matricole"][0] . "<ul>";
			echo "<li>controllo manuale: <a href='$report_file_url' target='_blank'>".$caso_test["matricole"][0].".pdf</a>: ";

			echo "</li>";

			echo "</ul></li>";
		}
		
	}

	
}
