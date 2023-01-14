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
    public \DateTime $data_laurea;
    public int $anno_immatricolazione;
    private GestioneCarrieraStudente $gestore_carriera;
    private ParametriConfigurazione $parametri_configurazione;

    public function __construct(int $matricola, string $CdL, \DateTime $data_laurea)
    {
        require_once("Esame.php");
        require_once("ParametriConfigurazione.php");
        require_once("GestioneCarrieraStudente.php");
        $this->gestore_carriera = GestioneCarrieraStudente::getInstance();
        $this->parametri_configurazione = ParametriConfigurazione::getInstance();

        $anagrafica = $this->gestore_carriera::getAnagrafica($matricola);
        $this->CdL = $CdL;
        $this->matricola = $matricola;
        $this->nome = $anagrafica["nome"];
        $this->cognome = $anagrafica["cognome"];
        $this->email = $anagrafica["email_ate"];
        $this->data_laurea = $data_laurea;
        $this->anno_immatricolazione = -1;

        $filtro_esami = array_values(array_filter(
            $this->parametri_configurazione::getFiltroEsami()[$CdL],
            function ($mat_i) use ($matricola) {
                return $mat_i == "*" || (int) $mat_i == $matricola;
            },
            ARRAY_FILTER_USE_KEY
        ));
        if (count($filtro_esami) == 2) {
            $filtro_esami = array_merge_recursive($filtro_esami[0], $filtro_esami[1]);
        } else {
            $filtro_esami = $filtro_esami[0];
        }

        $carriera = $this->gestore_carriera::getCarriera($matricola);
        $this->esami = array();
        foreach ($carriera as $esame) {
            if ($this->parametri_configurazione::getCorsiDiLaurea()[$CdL]["CdL-alt"] == $esame["CORSO"]) {
                $esame_nome = $esame["DES"];
                $esame_voto = (int) $esame["VOTO"];
                $esame_cfu = $esame["PESO"];
                $esame_data = $esame["DATA_ESAME"];
                $esame_in_cdl = !in_array($esame_nome, $filtro_esami["esami-non-cdl"]);
                $esame_in_avg = $esame_in_cdl && !in_array($esame_nome, $filtro_esami["esami-non-avg"]);
                $esame_in_inf = in_array($esame_nome, $this->parametri_configurazione::getEsamiInformatici());

                if ($this->anno_immatricolazione == -1) {
                    $this->anno_immatricolazione = (int)$esame["ANNO_IMM"];
                }

                $this->esami[] = new Esame(
                    $esame_nome,
                    $esame_voto,
                    $esame_cfu,
                    $esame_data,
                    $esame_in_cdl,
                    $esame_in_avg,
                    $esame_in_inf
                );
            }
        }
        if (count($this->esami) == 0) {
            throw new \Exception("Errore: Corso di laurea non corretto per la matricola $matricola.");
        }
    }

   /**
    * It calculates the weighted average of the exams
    *
    * @return float The weighted average of the exams.
    */
    public function getMediaPesata(): float
    {
        return array_reduce($this->esami, function ($acc, $esame) {
            return $acc + $esame->voto * $esame->cfu * $esame->in_avg;
        }, 0) / $this->getCFUInAVG();
    }

    /**
     * It returns the sum of the CFU of all the exams that are marked as "average"
     *
     * @return int The total number of CFU of the exams that are in the average.
     */
    public function getCFUInAVG(): int
    {
        return array_reduce($this->esami, function ($acc, $esame) {
            return $acc + $esame->cfu * $esame->in_avg;
        }, 0);
    }

    /**
     * It sums up the cfu of all the exams in the array
     *
     * @return int The total number of CFU of the exams in the list.
     */
    public function getCFU(): int
    {
        return array_reduce($this->esami, function ($acc, $esame) {
            return $acc + $esame->cfu * $esame->in_cdl;
        }, 0);
    }
}
