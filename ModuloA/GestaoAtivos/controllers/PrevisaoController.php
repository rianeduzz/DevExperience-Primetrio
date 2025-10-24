<?php
require_once(__DIR__ . '/../config/Database.php');

class PrevisaoController {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->verificarTabela();
    }

    private function verificarTabela() {
        try {
            $this->db->query("SELECT 1 FROM previsoes_manutencao LIMIT 1");
        } catch (Exception $e) {
            // Se a tabela não existir, retorna array vazio
            return [];
        }
    }

    public function analisarAtivos() {
        // Buscar histórico de manutenções dos últimos 12 meses
        $sql = "SELECT 
                    ativo_id,
                    COUNT(*) as total_manutencoes,
                    AVG(DATEDIFF(data, LAG(data) OVER (PARTITION BY ativo_id ORDER BY data))) as media_dias_entre_manutencoes,
                    SUM(custo) as custo_total
                FROM manutencoes
                WHERE data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY ativo_id";
        
        $historico = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($historico as $dados) {
            $probabilidade = $this->calcularProbabilidade($dados);
            if ($probabilidade > 70) { // Limite para considerar risco de falha
                $this->salvarPrevisao($dados['ativo_id'], $probabilidade);
            }
        }
    }

    private function calcularProbabilidade($dados) {
        // Lógica simplificada de IA para cálculo de probabilidade
        $score = 0;
        
        // Quanto mais manutenções, maior o risco
        if ($dados['total_manutencoes'] > 5) $score += 30;
        
        // Se a média de dias entre manutenções é baixa
        if ($dados['media_dias_entre_manutencoes'] < 30) $score += 40;
        
        // Se o custo total é alto
        if ($dados['custo_total'] > 5000) $score += 30;
        
        return $score;
    }

    public function listarPrevisoes() {
        try {
            $sql = "SELECT p.*, a.nome as nome_ativo 
                    FROM previsoes_manutencao p 
                    JOIN ativos a ON p.ativo_id = a.id 
                    WHERE p.data_previsao >= CURDATE() 
                    ORDER BY p.probabilidade_falha DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function salvarPrevisao($ativo_id, $probabilidade) {
        $sql = "INSERT INTO previsoes_manutencao 
                (ativo_id, probabilidade_falha, data_previsao, razoes) 
                VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?)";
        
        $razoes = "Previsão baseada em: histórico de manutenções, frequência e custos";
        $this->db->query($sql, [$ativo_id, $probabilidade, $razoes]);
    }
}
