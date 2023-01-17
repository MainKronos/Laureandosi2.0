<?php

namespace laureandosi;

class GestioneCarrieraStudente
{
    private static string $data_path;

    private static GestioneCarrieraStudente $instance;

    private function __construct()
    {
    }

    public static function getInstance(): GestioneCarrieraStudente
    {
        if (!isset(self::$instance)) {
            self::$data_path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'res', 'data'));
            self::$instance = new GestioneCarrieraStudente();
        }
        return self::$instance;
    }

    public static function getAnagrafica(int $matricola): string
    {
        return file_get_contents(self::$data_path . "/anagrafica_studenti.json");
    }

    public static function getCarriera(int $matricola): string
    {
        return file_get_contents(self::$data_path . "/carriera_studenti.json");
    }
}
