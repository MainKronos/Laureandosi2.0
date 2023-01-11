<?php

namespace laureandosi;

class GestioneCarrieraStudente
{
    private string $data_path;

    public function __construct()
    {
        $this->data_path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'res', 'data'));
    }

    public function getAnagrafica(int $matricola): array
    {
        $string = file_get_contents($this->data_path . "/anagrafica_studenti.json");
        return json_decode($string, true)["Entries"]["Entry"];
    }

    public function getCarriera(int $matricola): array
    {
        $string = file_get_contents($this->data_path . "/carriera_studenti.json");
        return json_decode($string, true)["Esami"]["Esame"];
    }
}
