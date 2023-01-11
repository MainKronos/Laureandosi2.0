<?php

namespace laureandosi;

class Esame
{
    public string $nome;
    public int $voto;
    public int $cfu;
    public string $data;

    public function __construct(string $nome, int $voto, int $cfu, string $data)
    {

        $this->nome = $nome;
        $this->voto = $voto;
        $this->cfu = $cfu;
        $this->data = $data;
    }
}
