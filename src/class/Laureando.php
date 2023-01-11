<?php

namespace laureandosi;

class Laureando
{
    public string $CdL;
    public int $matricola;
    public string $nome;
    public string $cognome;
    public string $email;
    public array $esami;

    public function __construct(int $matricola, string $CdL)
    {
        require_once("Esame.php");
        require_once("ParametriConfigurazione.php");
        require_once("GestioneCarrieraStudente.php");
        $gestore_carriera = new GestioneCarrieraStudente();
        $parametri_configurazione = new ParametriConfigurazione();

        $anagrafica = $gestore_carriera->getAnagrafica($matricola);
        $this->CdL = $CdL;
        $this->matricola = $matricola;
        $this->nome = $anagrafica["nome"];
        $this->cognome = $anagrafica["cognome"];
        $this->email = $anagrafica["email_ate"];

        $carriera = $gestore_carriera->getCarriera($matricola);
        $this->esami = array();
        foreach ($carriera as $esame) {
            if ($parametri_configurazione->getCorsiDiLaurea()[$CdL]["CdL-alt"] == $esame["CORSO"]) {
                $this->esami[] = new Esame($esame["DES"], (int) $esame["VOTO"], $esame["PESO"], $esame["DATA_ESAME"]);
            }
        }
    }
}
