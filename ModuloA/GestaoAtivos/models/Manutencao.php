<?php
class Manutencao {
    private $id;
    private $tipo; // preventiva ou corretiva
    private $data;
    private $responsavel_tecnico;
    private $custo;
    private $descricao;
    private $ativo_id;

    public function save(Database $db) {
        $sql = "INSERT INTO manutencoes (tipo, data, responsavel_tecnico, custo, descricao, ativo_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        return $db->query($sql, [
            $this->tipo,
            $this->data,
            $this->responsavel_tecnico,
            $this->custo,
            $this->descricao,
            $this->ativo_id
        ]);
    }

    // Getters e Setters
}
