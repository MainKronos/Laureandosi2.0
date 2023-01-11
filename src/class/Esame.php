<?php

namespace laureandosi;

class Esame
{
    public string $nome;
    public int $voto;
    public int $cfu;
    public string $data;
    public bool $is_avg;

    /**
     * @param string the name of the exam
     * @param int the grade you got
     * @param int the number of credits of the exam
     * @param string the date of the exam
     * @param bool if true, the average is calculated, otherwise the weighted average is
     * calculated
     */
    public function __construct(string $nome, int $voto, int $cfu, string $data, bool $is_avg)
    {
        $this->nome = $nome;
        $this->voto = $voto;
        $this->cfu = $cfu;
        $this->data = $data;
        $this->is_avg = $is_avg;
    }
}
