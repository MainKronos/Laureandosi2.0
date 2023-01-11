<?php

namespace laureandosi;

class API
{
    public function __construct()
    {
        require_once "ParametriConfigurazione.php";
        require_once "Laureando.php";
    }

    public function test()
    {
        return json_encode("test");
    }

    public function GETCorsiDiLaurea()
    {
        $parametri_configurazione = new ParametriConfigurazione();
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $val = array_values($parametri_configurazione->getCorsiDiLaurea());

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

            $laureandi = array();

            foreach ($data["matricole"] as $matricola) {
                $laureandi[] = new Laureando((int)$matricola, $data["corso_laurea"]);
            }
            return json_encode($laureandi);
        }
        return http_response_code(405);
    }
}
