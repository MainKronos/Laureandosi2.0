<?php

namespace laureandosi;

class ParametriConfigurazione
{
    private static string $path;

    private static ParametriConfigurazione $instance;

    private function __construct()
    {
    }

    public static function getInstance(): ParametriConfigurazione
    {
        if (!isset(self::$instance)) {
            self::$path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'res', 'config'));
            self::$instance = new ParametriConfigurazione();
        }
        return self::$instance;
    }

    public static function getEsamiInformatici(): array
    {
        return json_decode(
            file_get_contents(self::$path . DIRECTORY_SEPARATOR . "esami_informatici.json", true),
            true
        );
    }

    public static function getFiltroEsami(): array
    {
        return json_decode(
            file_get_contents(self::$path . DIRECTORY_SEPARATOR . "filtro_esami.json", true),
            true
        );
    }

    public static function getCorsiDiLaurea(): array
    {
        return json_decode(
            file_get_contents(self::$path . DIRECTORY_SEPARATOR . "corsi_di_laurea.json", true),
            true
        );
    }
}
