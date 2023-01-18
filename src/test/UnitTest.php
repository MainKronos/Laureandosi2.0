<?php

namespace test;
?>

<!DOCTYPE HTML>
<html lan="it">
<head>
	<meta charset="utf-8" />
	<title>Unit Test</title>
</head>
<body>
	<style media="only screen">
		html{
			background-color: black;
		}
		body {
			font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
			max-width: 21cm;
			margin: auto;
			background-color: white;
			padding: 20px;
		}
	</style>

<?php

namespace test;

class UnitTest
{
    private static $casi_test;
    private static \laureandosi\API $API;
    private static \laureandosi\ParametriConfigurazione $parametri_configurazione;

    public static function run()
    {
		echo "<h1>Unit Test</h1>";
		self::setUp();

		echo "<h2>Casi di test</h2>";

		echo "<pre><code>";
		print_r(json_encode(self::$casi_test, JSON_PRETTY_PRINT));
		echo "</code></pre>";

		echo "<hr>";
        self::testGETCorsiDiLaurea();
		echo "<hr>";
        self::testPOSTCreaReport();
		echo "<hr>";
        self::testGETApriReport();
		echo "<hr>";
        self::testInviaReport();
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
        echo "status:";

        echo "<details><summary>valore di ritorno: ";
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
        if (assert(strcmp($corsi_laurea, $val) == 0, "valore di ritorno")) {
            echo "<span style='color: green;'>OK</span>";
        }
        echo "</summary>";
        echo "<code>$string</code>";

        echo "</details>";
    }

    private static function testPOSTCreaReport()
    {
        echo "<h3>testPOSTCreaReport: </h3>";

        echo "status: <ul>";
        foreach (self::$casi_test as $caso_test) {
            $_SERVER["REQUEST_METHOD"] = "POST";
            $ret = self::$API::POSTCreaReport(json_encode($caso_test));

            echo "<li>Matricola: " . $caso_test["matricole"][0];

            echo "<details><summary>valore di ritorno: ";
            if (assert(!strpos(json_decode($ret, true)["message"], 'ERROR'), "valore di ritorno")) {
                echo "<span style='color: green;'>OK</span>";
            }
            echo "</summary>";
            echo "<code>$ret</code>";
            echo "</details>";

            echo "<details><summary>creazione cartella: ";
            $report_path = join(DIRECTORY_SEPARATOR, array(
                rtrim(ABSPATH, '/'),
                "report",
                $caso_test["data_laurea"],
                $caso_test["corso_laurea"],
            ));
            if (assert(file_exists($report_path), "creazione cartella")) {
                echo "<span style='color: green;'>OK</span>";
            }
            echo "</summary>";
            echo "<code>$report_path</code>";
            echo "</details>";

            echo "<details><summary>creazione file laureando: ";
            $report_file_laureando = join(DIRECTORY_SEPARATOR, array(
                $report_path,
                $caso_test["matricole"][0] . ".pdf"
            ));
            if (assert(file_exists($report_file_laureando), "creazione file")) {
                echo "<span style='color: green;'>OK</span>";
            }
            echo "</summary>";
            echo "<code>$report_file_laureando</code>";
            echo "</details>";

            echo "<details><summary>creazione file commissione: ";
            $report_file_commissione = join(DIRECTORY_SEPARATOR, array(
                $report_path,
                "all.pdf"
            ));
            if (assert(file_exists($report_file_commissione), "creazione file")) {
                echo "<span style='color: green;'>OK</span>";
            }
            echo "</summary>";
            echo "<code>$report_file_commissione</code>";
            echo "</details>";

            echo "</li>";
        }
        echo "</ul>";
    }

    private static function testGETApriReport()
    {
        echo "<h3>testGETApriReport: </h3>";

        echo "status: <ul>";

        for ($i = 0; $i < count(self::$casi_test); $i++) {
            $caso_test = self::$casi_test[$i];


            $report_file_url = join(DIRECTORY_SEPARATOR, array(
                "..",
                "report",
                $caso_test["data_laurea"],
                $caso_test["corso_laurea"],
                $caso_test["matricole"][0] . "_sim.pdf"
            ));

            $report_file = join(DIRECTORY_SEPARATOR, array(
                rtrim(ABSPATH, '/'),
                "report",
                $caso_test["data_laurea"],
                $caso_test["corso_laurea"],
                $caso_test["matricole"][0] . "_sim.pdf"
            ));

            $laureando = $caso_test["corso_laurea"] != "t-inf" ?
                new \laureandosi\Laureando($caso_test["matricole"][0], $caso_test["corso_laurea"], date_create($caso_test["data_laurea"])) :
                new \laureandosi\LaureandoInformatica($caso_test["matricole"][0], $caso_test["corso_laurea"], date_create($caso_test["data_laurea"]));

            (new \laureandosi\ReportPDFLaureandoConSimulazione($laureando))->genera()->salva($report_file);

            echo "<li>Matricola: " . $caso_test["matricole"][0] ;
            echo "<details><summary>controllo manuale: <a href='$report_file_url' target='_blank'>" . $caso_test["matricole"][0] . ".pdf</a>: ";

            echo "</summary>";
            echo "<code>$report_file</code>";
            echo "</details>";

            echo "</li>";
        }
        echo "</ul>";
    }

    private static function testInviaReport()
    {
        echo "<h3>testInviaReport: </h3>";

        echo "status:";

        $_SERVER["REQUEST_METHOD"] = "POST";
        $caso_test = array(
            "matricola" => self::$casi_test[0]["matricole"][0],
            "corso_laurea" => self::$casi_test[0]["corso_laurea"],
            "data_laurea" => self::$casi_test[0]["data_laurea"],
        );

        $cache_file = join(DIRECTORY_SEPARATOR, array(
            rtrim(ABSPATH, '/'),
            "report",
            $caso_test["data_laurea"],
            $caso_test["corso_laurea"],
            "stato_invio.json"
        ));

        if (file_exists($cache_file)) {
            unlink($cache_file);
        }

        $ret = self::$API::POSTInviaReport(json_encode($caso_test));

        echo "<details><summary>valore di ritorno: ";
        if (assert(!strpos(json_decode($ret, true)["message"], 'ERROR'), "valore di ritorno")) {
            echo "<span style='color: green;'>OK</span>";
        }
        echo "</summary>";
        echo "<code>$ret</code>";
        echo "</details>";

        echo "<details><summary>creazione file cache: ";
        if (assert(file_exists($cache_file), "creazione file cache")) {
            echo "<span style='color: green;'>OK</span>";
        }
        echo "</summary>";
        echo "<code>$cache_file</code>";
        echo "</details>";

        echo "<details><summary>controllo invio email in file cache: ";
        $string = file_get_contents($cache_file, true);
        if(assert(json_decode($string, true)[(string)$caso_test["matricola"]], "controllo invio email in file cache")){
         echo "<span style='color: green;'>OK</span>";
        }
        echo "</summary>";
        echo "<code>$string</code>";
        echo "</details>";

        echo "<details><summary>controllo doppio invio email: ";
        $ret = self::$API::POSTInviaReport(json_encode($caso_test));
        if(assert(strpos($ret, "Report gi\u00e0 inviato"), "controllo doppio invio email")){
         echo "<span style='color: green;'>OK</span>";
        }
        echo "</summary>";
        echo "<code>$ret</code>";
        echo "</details>";
    }
}

?>
</body>
</html>