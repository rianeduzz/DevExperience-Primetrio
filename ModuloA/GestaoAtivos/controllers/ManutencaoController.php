<?php
require_once(__DIR__ . '/../config/Database.php');

class ManutencaoController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function salvarManutencao($dados) {
        try {
            $sql = "INSERT INTO manutencoes (ativo_id, tipo, data, responsavel_id, custo, descricao) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $params = [
                intval($dados['ativo_id']),
                $dados['tipo'],
                $dados['data'],
                $_SESSION['user_id'],
                floatval($dados['custo']),
                $dados['descricao']
            ];

            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erro ao salvar manutenção: ' . $e->getMessage());
        }
    }
}
