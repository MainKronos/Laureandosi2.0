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
    public string $data_laurea;
    private GestioneCarrieraStudente $gestore_carriera;
    private ParametriConfigurazione $parametri_configurazione;

    public function __construct(int $matricola, string $CdL, string $data_laurea)
    {
        require_once("Esame.php");
        require_once("ParametriConfigurazione.php");
        require_once("GestioneCarrieraStudente.php");
        $this->gestore_carriera = new GestioneCarrieraStudente();
        $this->parametri_configurazione = new ParametriConfigurazione();

        $anagrafica = $this->gestore_carriera->getAnagrafica($matricola);
        $this->CdL = $CdL;
        $this->matricola = $matricola;
        $this->nome = $anagrafica["nome"];
        $this->cognome = $anagrafica["cognome"];
        $this->email = $anagrafica["email_ate"];
        $this->data_laurea = $data_laurea;

        $carriera = $this->gestore_carriera->getCarriera($matricola);
        $this->esami = array();
        foreach ($carriera as $esame) {
            if ($this->parametri_configurazione->getCorsiDiLaurea()[$CdL]["CdL-alt"] == $esame["CORSO"]) {
                $this->esami[] = new Esame(
                    $esame["DES"],
                    (int) $esame["VOTO"],
                    $esame["PESO"],
                    $esame["DATA_ESAME"],
                    true
                );
            }
        }
    }

   /**
    * It calculates the weighted average of the exams
    *
    * @return float The weighted average of the exams.
    */
    public function getMediaPesata(): float
    {
        $media = 0;
        $cfu = 0;
        foreach ($this->esami as $esame) {
            if ($esame->is_avg) {
                $media += $esame->voto * $esame->cfu;
                $cfu += $esame->cfu;
            }
        }
        return $media / $cfu;
    }

    /**
     * It returns the sum of the CFU of all the exams that are marked as "average"
     *
     * @return int The total number of CFU of the exams that are in the average.
     */
    public function getCFUInAVG(): int
    {
        $cfu = 0;
        foreach ($this->esami as $esame) {
            if ($esame->is_avg) {
                $cfu += $esame->cfu;
            }
        }
        return $cfu;
    }

    /**
     * It sums up the cfu of all the exams in the array
     *
     * @return int The total number of CFU of the exams in the list.
     */
    public function getCFU(): int
    {
        $cfu = 0;
        foreach ($this->esami as $esame) {
            $cfu += $esame->cfu;
        }
        return $cfu;
    }
}
