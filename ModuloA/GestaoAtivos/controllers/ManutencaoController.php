<?php
require_once(__DIR__ . '/../config/Database.php');
require_once(__DIR__ . '/../models/Manutencao.php');

class ManutencaoController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function registrar($dados) {
        $manutencao = new Manutencao();
        // Preencher dados da manutenção
        $resultado = $manutencao->save($this->db);
        
        return [
            'success' => $resultado,
            'message' => $resultado ? 'Manutenção registrada com sucesso' : 'Erro ao registrar manutenção'
        ];
    }

    public function listarAtivos() {
        return $this->db->query("SELECT id, nome FROM ativos")->fetchAll();
    }

    public function listarManutencoes() {
        // Retorne um array de manutenções (exemplo estático)
        return [
            [
                'id' => 1,
                'ativo' => 'Ativo 1',
                'tipo' => 'Corretiva',
                'data' => '2024-06-10',
                'responsavel' => 'João',
                'custo' => 500,
                'descricao' => 'Troca de peça'
            ],
            [
                'id' => 2,
                'ativo' => 'Ativo 2',
                'tipo' => 'Preventiva',
                'data' => '2024-06-05',
                'responsavel' => 'Maria',
                'custo' => 200,
                'descricao' => 'Lubrificação'
            ]
        ];
    }
}
