<?php

namespace laureandosi;

class ParametriConfigurazione
{
    private string $path;

    public function __construct()
    {
        $this->path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'res', 'config'));
    }

    public function getEsamiInformatici()
    {
        return json_decode(
            file_get_contents($this->path . DIRECTORY_SEPARATOR . "filtro_esami.json", true),
            true
        );
    }

    public function getFiltroEsami()
    {
        return json_decode(
            file_get_contents($this->path . DIRECTORY_SEPARATOR . "filtro_esami.json", true),
            true
        );
    }

    public function getCorsiDiLaurea()
    {
        return json_decode(
            file_get_contents($this->path . DIRECTORY_SEPARATOR . "corsi_di_laurea.json", true),
            true
        );
    }
}
